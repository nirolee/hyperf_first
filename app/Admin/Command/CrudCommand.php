<?php

declare(strict_types=1);

namespace App\Admin\Command;

use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Database\Commands\Ast\ModelRewriteConnectionVisitor;
use Hyperf\Database\Commands\Ast\ModelUpdateVisitor;
use Hyperf\Database\Commands\ModelData;
use Hyperf\Database\Commands\ModelOption;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Schema\Column;
use Hyperf\Database\Schema\MySqlBuilder;
use Hyperf\Utils\CodeGen\Project;
use Hyperf\Utils\Str;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @Command
 */
class CrudCommand extends HyperfCommand
{
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * @var ConnectionResolverInterface
     */
    protected ConnectionResolverInterface $resolver;

    /**
     * @var ConfigInterface
     */
    protected ConfigInterface $config;

    /**
     * @var ModelOption
     */
    protected ModelOption $options;

    /**
     * @var \PhpParser\Parser
     */
    protected \PhpParser\Parser $astParser;

    /**
     * @var \PhpParser\PrettyPrinterAbstract
     */
    protected \PhpParser\PrettyPrinterAbstract $printer;

    /**
     * model模板变量配置
     * @var array
     */
    protected array $modelStubVars = [
        'namespace' => 'App\Common\Model',
        'uses' => []
    ];

    /**
     * @var array form模板变量配置
     */
    protected array $formStubVars = [
        'namespace' => 'App\Admin\Form',
        'uses' => [
            'use App\Admin\Library\AdminForm;'
        ],
        'parentForm' => 'AdminForm'
    ];

    /**
     * @var array service模板变量配置
     */
    protected array $serviceStubVars = [
        'namespace' => 'App\Admin\Service',
        'uses' => [
            'use App\Admin\Constants\ErrorCode;',
            'use Hyperf\Di\Annotation\Inject;'
        ],
    ];

    protected array $controllerStubVars = [
        'namespace' => 'App\Admin\Controller',
        'uses' => [
            'use App\Admin\Annotation\AdminController;',
            'use App\Admin\Constants\ErrorCode;',
            'use Hyperf\Di\Annotation\Inject;',
            'use Hyperf\HttpServer\Annotation\GetMapping;',
            'use Hyperf\HttpServer\Annotation\PostMapping;',
            'use Hyperf\HttpServer\Annotation\RequestMapping;',
            'use App\Admin\Library\MyController;'
        ],
        'parentClass' => 'MyController'
    ];

    /**
     * @var array 表字段信息
     */
    protected array $columns = [];

    /**
     * @var string 主键字段
     */
    protected string $pkColumn = '';

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('gen:crud');
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        $this->resolver = $this->container->get(ConnectionResolverInterface::class);
        $this->config = $this->container->get(ConfigInterface::class);
        $this->astParser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);
        $this->printer = new Standard();

        return parent::run($input, $output);
    }

    public function configure()
    {
        parent::configure();
        $this->addArgument('table', InputArgument::REQUIRED, '表名');
        $this->addArgument('model', InputArgument::OPTIONAL, 'model名', '');

        $this->addOption('moduleName', 'm', InputOption::VALUE_OPTIONAL, '模块名称', ADMIN_SERVER);
        $this->addOption('pool', 'p', InputOption::VALUE_OPTIONAL, 'Which connection pool you want the Model use.', 'default');
        $this->addOption('path', null, InputOption::VALUE_OPTIONAL, 'The path that you want the Model file to be generated.');
        $this->addOption('force-casts', 'F', InputOption::VALUE_NONE, 'Whether force generate the casts for model.');
        $this->addOption('prefix', 'P', InputOption::VALUE_OPTIONAL, 'What prefix that you want the Model set.');
        $this->addOption('inheritance', 'i', InputOption::VALUE_OPTIONAL, 'The inheritance that you want the Model extends.');
        $this->addOption('uses', 'U', InputOption::VALUE_OPTIONAL, 'The default class uses of the Model.');
        $this->addOption('refresh-fillable', null, InputOption::VALUE_NONE, 'Whether generate fillable argement for model.');
        $this->addOption('table-mapping', 'M', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Table mappings for model.');
        $this->addOption('ignore-tables', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Ignore tables for creating models.');
        $this->addOption('with-comments', null, InputOption::VALUE_NONE, 'Whether generate the property comments for model.');
        $this->addOption('visitors', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Custom visitors for ast traverser.');
        $this->addOption('property-case', null, InputOption::VALUE_OPTIONAL, 'Which property case you want use, 0: snake case, 1: camel case.');
    }

    public function handle()
    {
        $tableName = $this->input->getArgument('table');
        $modelName = $this->input->getArgument('model');
        $this->initOptions();
        $this->initColumnsSchema($tableName);
        $modelName = $modelName ? $modelName : Str::studly(Str::replaceFirst($this->options->getPrefix(), '', $tableName));

        $modelOper = $this->output->ask('是否只生成model?[0: 否, 1: 是, 2: 跳过]', 0);
        switch ($modelOper) {
            case 0:
                $this->createModel($tableName, $modelName);
                break;
            case 1:
                $this->createModel($tableName, $modelName);
                return true;
            default:
                break;
        }
        $this->createForm($modelName);
        $this->createService($modelName);
        $this->createController($modelName);
        $this->createViews($modelName);
    }

    /**
     * describe 获取表信息
     * author derick
     * date 2020/3/26
     * @param string $table
     * @return array
     */
    protected function getTableColumns(string $table)
    {
        $builder = $this->getSchemaBuilder($this->options->getPool());
        return $this->formatColumns($builder->getColumnTypeListing($table));
    }

    /**
     * describe 获取数据库连接
     * author derick
     * date 2020/3/26
     * @param string $poolName
     * @return MySqlBuilder
     */
    protected function getSchemaBuilder(string $poolName): MySqlBuilder
    {
        $connection = $this->resolver->connection($poolName);
        return $connection->getSchemaBuilder();
    }

    /**
     * describe 获取可选参数
     * author derick
     * date 2020/3/26
     * @param string $name
     * @param string $key
     * @param string $pool
     * @param null $default
     * @return bool|string|string[]|null
     */
    protected function getOption(string $name, string $key, string $pool = 'default', $default = null)
    {
        $result = $this->input->getOption($name);
        $nonInput = null;
        if (in_array($name, ['force-casts', 'refresh-fillable', 'with-comments'])) {
            $nonInput = false;
        }
        if (in_array($name, ['table-mapping', 'ignore-tables', 'visitors'])) {
            $nonInput = [];
        }

        if ($result === $nonInput) {
            $result = $this->config->get("databases.{$pool}.{$key}", $default);
        }

        return $result;
    }

    /**
     * Format column's key to lower case.
     * @param array $columns
     * @return array
     */
    protected function formatColumns(array $columns): array
    {
        return array_map(function ($item) {
            return array_change_key_case($item, CASE_LOWER);
        }, $columns);
    }

    protected function getColumns($className, $columns, $forceCasts): array
    {
        /** @var Model $model */
        $model = new $className();
        $dates = $model->getDates();
        $casts = [];
        if (!$forceCasts) {
            $casts = $model->getCasts();
        }

        foreach ($dates as $date) {
            if (!isset($casts[$date])) {
                $casts[$date] = 'datetime';
            }
        }

        foreach ($columns as $key => $value) {
            $columns[$key]['cast'] = $casts[$value['column_name']] ?? null;
        }

        return $columns;
    }

    /**
     * describe 生成model
     * author derick
     * date 2020/3/26
     * @param string $table
     * @param string $model
     * @return bool
     */
    protected function createModel(string $table, string $model)
    {
        $table = Str::replaceFirst($this->options->getPrefix(), '', $table);
        $filePath = BASE_PATH . '/' . $this->options->getPath() . '/' . $model . '.php';
        if (file_exists($filePath)) {
            rename($filePath, BASE_PATH . '/' . $this->options->getPath() . '/' . $model . '.php.bak');
//            $this->line("\n!!!!!!!!!!!!!!$filePath exists, generate error, please check model file content!!!!!!!!!!!!!!\n");
//            return false;
        }
        $this->createFolder($filePath);
        $stub = file_get_contents(__DIR__ . '/template/Model.stub');

        $addFunctionDocParams = $addFunctionParams = $addVars = $whereConditionArr = [];
        foreach ($this->columns as $c) {
            $dataType = $this->formatDatabaseType($c['dataType']);
            if (in_array($dataType, ['int', 'float'])) {
                $_where = 'isset($where[\'' . $c['name'] . '\']) && is_numeric($where[\'' . $c['name'] . '\'])';
            } else {
                $_where = 'isset($where[\'' . $c['name'] . '\']) && $where[\'' . $c['name'] . '\']';
            }
            $whereConditionArr[] = 'if (' . $_where . ') {' . "\n\t\t\t" . '$builder->where(\''.$c['name'].'\', \'=\', $where[\''.$c['name'].'\']);' . "\n\t\t}";
            if ($c['isPrimaryKey'] && $c['pkAutoIncrement']) {
                continue;
            }
            $varName = Str::camel(Str::studly(Str::singular($c['name'])));
            $addFunctionDocParams[] = '* @param ' . $dataType . ' $' . $varName . ' ' . $c['comment'];
            $addFunctionParams[] = $dataType . ' $' . $varName;
            $addVars[] = "'" . $c['name'] . "' => $" . $varName;
        }

        $stub = str_replace([
            '%NAMESPACE%',
            '%CLASS%',
            '%INHERITANCE%',
            '%TABLE%',
            '%CONNECTION%',
            '%USES%',
            '%DATE%',
            '%PK%',
//            '%ADD_FUNCTION_DOC_PARAMS%',
//            '%ADD_FUNCTION_PARAMS%',
//            '%ADD_FUNCTION_VARS_ARRAY%',
            '%WHERE_CONDITION%',
        ], [
            $this->modelStubVars['namespace'],
            $model,
            $this->options->getInheritance(),
            $table,
            $this->options->getPool(),
            $this->modelStubVars['uses'] ? implode(PHP_EOL, $this->modelStubVars['uses']) : '',
            date('Y/m/d'),
            $this->pkColumn,
//            $addFunctionDocParams ? implode(PHP_EOL . "\t\t", $addFunctionDocParams) : '',
//            $addFunctionParams ? implode(', ', $addFunctionParams) : '',
//            $addVars ? implode(',', $addVars) : '',
            $whereConditionArr ? implode(PHP_EOL . "\t\t", $whereConditionArr) : '',
        ], $stub);

        file_put_contents($filePath, $stub);

        $project = new Project();
//        $model = $this->options->getTableMapping()[$table] ?? Str::studly(Str::singular($table));
        $model = $project->namespace($this->options->getPath()) . $model;
        $columns = $this->getTableColumns($table);
        $columns = $this->getColumns($model, $columns, $this->options->isForceCasts());
        $stms = $this->astParser->parse(file_get_contents($filePath));
        $traverser = new NodeTraverser();
        $traverser->addVisitor(make(ModelUpdateVisitor::class, [
            'class' => $model,
            'columns' => $columns,
            'option' => $this->options,
        ]));
        $traverser->addVisitor(make(ModelRewriteConnectionVisitor::class, [$model, $this->options->getPool()]));
        foreach ($this->options->getVisitors() as $visitorClass) {
            $data = make(ModelData::class)->setClass($model)->setColumns($columns);
            $traverser->addVisitor(make($visitorClass, [$this->options, $data]));
        }
        $stms = $traverser->traverse($stms);
        $code = $this->printer->prettyPrintFile($stms);

        file_put_contents($filePath, $code);
        $this->line('model generate success.');
    }

    /**
     * describe 生成form
     * author derick
     * date 2020/3/28
     * @param string $model
     * @return bool
     */
    protected function createForm(string $model)
    {
        $filePath = BASE_PATH . '/' . Str::camel(str_replace('\\', '/', $this->formStubVars['namespace'])) . '/' . $model . 'Form.php';
        if (file_exists($filePath)) {
            $this->line("\n!!!!!!!!!!!!!!$filePath exists, generate error, please check form file content!!!!!!!!!!!!!!\n");
            return false;
        }
        $this->createFolder($filePath);
        $stub = file_get_contents(__DIR__ . '/template/Form.stub');

        $attributes = [];
        $this->line("\n" . 'please add below array key into dbcolumn.php file');
        $addRules = $editRules = $deleteRules = [];
        $listRules = ["'page' => 'integer'", "'limit' => 'integer'"];
        $lmodel = strtolower($model);
        foreach ($this->columns as $c) {
            $_k = $lmodel . '_' . $c['name'];
            $this->line("'" . $_k . "' => '',");
            $attributes[] = '\'' . $c['name'] . '\' => trans(\'dbcolumn.' . $_k . '\')';
            $listRules[] = "'" . $c['name'] . "' => ['string', 'nullable']";
            $rules = [];
            if ($c['isNullAble']) {
                $rules[] = 'required';
            }
            if (in_array($c['dataType'], ['int', 'tinyint', 'smallint', 'mediumint', 'bigint'])) {
                $unsignedPrefix = '';
                if ($c['unsigned']) {
                    $unsignedPrefix = 'unsigned';
                }
                $rules[] = $unsignedPrefix . $c['dataType'];
            } elseif (in_array($c['dataType'], ['char', 'varchar'])) {
                $rules[] = 'max:' . $c['maxLength'];
            }

            if (empty($rules)) {
                continue;
            }

            if ($c['isPrimaryKey'] && $c['pkAutoIncrement']) {
                $editRules[] = "'" . $c['name'] . "' => ['".implode('\',\'', $rules)."']";
                $deleteRules[] = "'" . $c['name'] . "' => ['".implode('\',\'', $rules)."']";
                continue;
            }
            $addRules[] = "'" . $c['name'] . "' => ['".implode('\',\'', $rules)."']";
            $editRules[] = "'" . $c['name'] . "' => ['".implode('\',\'', $rules)."']";
        }
        $this->line("");

        $stub = str_replace([
            '%NAMESPACE%',
            '%CLASS%',
            '%USES%',
            '%PARENT_FORM%',
            '%ATTRIBUTES%',
            '%LIST_RULES%',
            '%ADD_RULES%',
            '%EDIT_RULES%',
            '%DELETE_RULES%',
        ], [
            $this->formStubVars['namespace'],
            $model,
            $this->formStubVars['uses'] ? implode(PHP_EOL, $this->formStubVars['uses']) : '',
            $this->formStubVars['parentForm'],
            $attributes ? implode(', ' . "\n\t\t\t", $attributes) : '',
            $listRules ? implode(', ' . "\n\t\t\t\t", $listRules) : '',
            $addRules ? implode(', ' . "\n\t\t\t\t", $addRules) : '',
            $editRules ? implode(', ' . "\n\t\t\t\t", $editRules) : '',
            $deleteRules ? implode(', ' . "\n\t\t\t\t", $deleteRules) : '',
        ], $stub);

        file_put_contents($filePath, $stub);

        $this->line('form generate success.');
    }

    /**
     * describe 生成service
     * author derick
     * date 2020/3/28
     * @param string $model
     * @return bool
     */
    protected function createService(string $model)
    {
        $filePath = BASE_PATH . '/' . Str::camel(str_replace('\\', '/', $this->serviceStubVars['namespace'])) . '/' . $model . 'Service.php';
        if (file_exists($filePath)) {
            $this->line("\n!!!!!!!!!!!!!!$filePath exists, generate error, please check service file content!!!!!!!!!!!!!!\n");
            return false;
        }
        $this->createFolder($filePath);
        $stub = file_get_contents(__DIR__ . '/template/Service.stub');

        $attributes = $updateArrs = [];
        foreach ($this->columns as $c) {
            if ($c['isPrimaryKey']) {
                continue;
            }
            if ($this->formatDatabaseType($c['dataType']) == 'int') {
                $attributes[] = 'intval($data[\'' . $c['name'] . '\'])';
            } else {
                $attributes[] = '$data[\'' . $c['name'] . '\']';
            }
            $updateArrs[] = 'if (isset($data[\'' . $c['name'] . '\'])) {' . "\n\t\t\t" . '$updateArray[\'' . $c['name'] . '\'] = $data[\'' . $c['name'] . '\'];' . "\n\t\t}";
        }
//        $this->serviceStubVars['uses'][] = 'use '.$this->formStubVars['namespace'].'\\'.$model.'Form;';
        $this->serviceStubVars['uses'][] = 'use ' . $this->modelStubVars['namespace'] . '\\' . $model . ';';

        $stub = str_replace([
            '%NAMESPACE%',
            '%CLASS%',
            '%DATE%',
            '%USES%',
            '%PK%',
//            '%ADD_PARAMS%',
            '%UPDATE_WHERE_VARS%'
        ], [
            $this->serviceStubVars['namespace'],
            $model,
            date('Y/m/d'),
            implode(PHP_EOL, $this->serviceStubVars['uses']),
            $this->pkColumn,
//            $attributes ? implode(', ', $attributes) : '',
            $updateArrs ? implode(PHP_EOL . "\t\t", $updateArrs) : '',
        ], $stub);

        file_put_contents($filePath, $stub);

        $this->line('service generate success.');
    }

    /**
     * describe 生成controller
     * author derick
     * date 2020/3/28
     * @param string $model
     * @return bool
     */
    protected function createController(string $model)
    {
        $filePath = BASE_PATH . '/' . Str::camel(str_replace('\\', '/', $this->controllerStubVars['namespace'])) . '/' . $model . 'Controller.php';
        if (file_exists($filePath)) {
            $this->line("\n!!!!!!!!!!!!!!$filePath exists, generate error, please check controller file content!!!!!!!!!!!!!!\n");
            return false;
        }
        $this->createFolder($filePath);
        $stub = file_get_contents(__DIR__ . '/template/Controller.stub');

        $this->controllerStubVars['uses'][] = 'use ' . $this->formStubVars['namespace'] . '\\' . $model . 'Form;';
        $this->controllerStubVars['uses'][] = 'use ' . $this->serviceStubVars['namespace'] . '\\' . $model . 'Service;';
        $this->controllerStubVars['uses'][] = 'use ' . $this->modelStubVars['namespace'] . '\\' . $model . ';';

        $stub = str_replace([
            '%NAMESPACE%',
            '%CLASS%',
            '%DATE%',
            '%USES%',
            '%PARENT_CLASS%',
            '%PREFIX%',
            '%PK%'
        ], [
            $this->controllerStubVars['namespace'],
            $model,
            date('Y/m/d'),
            implode(PHP_EOL, $this->controllerStubVars['uses']),
            $this->controllerStubVars['parentClass'],
            strtolower($model),
            $this->pkColumn
        ], $stub);

        file_put_contents($filePath, $stub);

        $this->line('controller generate success.');
    }

    /**
     * describe 生成view
     * author derick
     * date 2020/3/28
     * @param string $model
     * @return bool
     */
    protected function createViews(string $model)
    {
        $lmodel = strtolower($model);
        $viewFolder = config('view.config.' . $this->input->getOption('moduleName') . '.view_path', '');
        if (empty($viewFolder)) {
            $this->line('view folder path not found, please check view.php config');
            return false;
        }
        $filePath = $viewFolder . $lmodel . '/add.blade.php';
        $this->createFolder($filePath);
        if (file_exists($filePath)) {
            $this->line("\n!!!!!!!!!!!!!!$filePath exists, generate error, please check add.blade.php file!!!!!!!!!!!!!!\n");
        } else {
            $stub = file_get_contents(__DIR__ . '/template/Add.stub');
            $stub = str_replace([
                '%CLASS%',
            ], [
                $lmodel,
            ], $stub);
            file_put_contents($filePath, $stub);
            $this->line('add.blade.php generate success.');
        }

        $filePath = $viewFolder . $lmodel . '/edit.blade.php';
        if (file_exists($filePath)) {
            $this->line("\n!!!!!!!!!!!!!!$filePath exists, generate error, please check edit.blade.php file!!!!!!!!!!!!!!\n");
        } else {
            $stub = file_get_contents(__DIR__ . '/template/Edit.stub');
            $stub = str_replace([
                '%CLASS%',
            ], [
                $lmodel,
            ], $stub);
            file_put_contents($filePath, $stub);
            $this->line('edit.blade.php generate success.');
        }

        $filePath = $viewFolder . $lmodel . '/_form.blade.php';
        if (file_exists($filePath)) {
            $this->line("\n!!!!!!!!!!!!!!$filePath exists, generate error, please check edit.blade.php file!!!!!!!!!!!!!!\n");
        } else {
            $stub = file_get_contents(__DIR__ . '/template/FormHtml.stub');

            $content = [];
            foreach ($this->columns as $c) {
                if ($c['isPrimaryKey']) {
                    $content[] = '<input type="hidden" name="' . $c['name'] . '" value="{{isset($obj) && isset($obj[\'' . $c['name'] . '\']) ? $obj[\'' . $c['name'] . '\'] : \'\'}}">';
                } else {
                    $transColumnKey = $lmodel . '_' . $c['name'];
                    if ($c['isNullAble']) {
                        $required = '<i class="required-color">*</i>';
                        $inputRequired = 'lay-verify="required"';
                    } else {
                        $required = $inputRequired = '';
                    }
                    $input = PHP_EOL . "\t\t\t" . '<input type="text" name="' . $c['name'] . '" ' . $inputRequired . ' autocomplete="off" placeholder="{{trans(\'message.please_enter\').trans(\'dbcolumn.' . $transColumnKey . '\')}}" class="layui-input" value="{{isset($obj) && isset($obj[\'' . $c['name'] . '\']) ? $obj[\'' . $c['name'] . '\'] : \'\'}}">' . PHP_EOL . "\t\t";
                    $content[] = '<div class="layui-form-item">' . PHP_EOL . "\t\t" . '<label class="layui-form-label">' . $required . '{{trans(\'dbcolumn.' . $transColumnKey . '\')}}</label>' . PHP_EOL . "\t\t" . '<div class="layui-input-inline">' . $input . '</div>' . PHP_EOL . "\t\t" . '<div class="layui-form-mid required-color" id="tips_' . $c['name'] . '"></div>' . PHP_EOL . "\t" . '</div>';
                }
            }
            $stub = str_replace([
                '%CLASS%',
                '%FORM_CONTENT%',
                '%PREFIX%'
            ], [
                $model,
                $content ? implode("\n\t", $content) : '',
                $lmodel
            ], $stub);
            file_put_contents($filePath, $stub);
            $this->line('_form.blade.php generate success.');
        }

        $filePath = $viewFolder . $lmodel . '/index.blade.php';
        if (file_exists($filePath)) {
            $this->line("\n!!!!!!!!!!!!!!$filePath exists, generate error, please check index.blade.php file!!!!!!!!!!!!!!\n");
        } else {
            $stub = file_get_contents(__DIR__ . '/template/Index.stub');
            $fields = $queryFormCondition = [];
            foreach ($this->columns as $c) {
                $transColumnKey = $lmodel . '_' . $c['name'];
                if ($c['isPrimaryKey']) {
                    $fields[] = '{field:"' . $c['name'] . '", title: "ID", sort: true}';
                } else {
                    $fields[] = '{field:"' . $c['name'] . '", title: "{{trans(\'dbcolumn.' . $transColumnKey . '\')}}"}';
                }
                $queryFormCondition[] = '<div class="layui-inline">
	        <label class="layui-form-label">{{trans(\'dbcolumn.' . $transColumnKey . '\')}}</label>
            <div class="layui-input-inline">
                <input type="text" name="' . $c['name'] . '" autocomplete="off" class="layui-input">
            </div>
        </div>';
            }
            $deleteBtnClass = 'delete' . $model . 'Btn';
            $fields[] = '{field: "operating", title: "{{trans(\'message.operate\')}}", align: "center", templet : function(data){
                    var html = "<a class=\'layui-btn permission-btn layui-btn-xs edit' . $model . 'Btn\' href=\\"{{url(\'/' . $lmodel . '/edit\')}}?id="+data.' . $this->pkColumn . '+"\\">{{trans(\'message.edit\')}}</a>";
                    html += "<a class=\'layui-btn permission-btn layui-btn-xs ' . $deleteBtnClass . '\' data-id=\'"+data.' . $this->pkColumn . '+"\'>{{trans(\'message.delete\')}}</a>";
                return html;
            }}';
            $stub = str_replace([
                '%CLASS%',
                '%MODEL%',
                '%FIELDS%',
                '%DELETEBTN%',
                '%PK%',
                '%QUERY_CONDITION%'
            ], [
                $model,
                $lmodel,
                $fields ? implode("\n\t\t\t\t,", $fields) : '',
                $deleteBtnClass,
                $this->pkColumn,
                $queryFormCondition ? implode("\n\t\t", $queryFormCondition) : '',
            ], $stub);
            file_put_contents($filePath, $stub);
            $this->line('index.blade.php generate success.');
            $this->line("\nplease add below key \n'add_" . $lmodel . "' => '',\ninto message.php languages file\n");
        }

    }

    /**
     * describe 将mysql数据类型转换成php数据类型
     * author derick
     * date 2020/3/28
     * @param string $type
     * @return string|null
     */
    protected function formatDatabaseType(string $type): ?string
    {
        switch ($type) {
            case 'tinyint':
            case 'smallint':
            case 'mediumint':
            case 'int':
            case 'bigint':
                return 'int';
            case 'decimal':
            case 'float':
            case 'double':
            case 'real':
                return 'float';
            case 'bool':
            case 'boolean':
                return 'boolean';
            case 'varchar':
            case 'char':
            case 'longtext':
            case 'mediumtext':
            case 'text':
            case 'tinytext':
                return 'string';
            default:
                return null;
        }
    }

    /**
     * describe 初始化数据库配置
     * author derick
     * date 2020/3/26
     * @return ModelOption
     */
    private function initOptions()
    {
        $pool = $this->input->getOption('pool');
        $this->options = new ModelOption();
        $this->options->setPool($pool)
            ->setPath($this->getOption('path', 'commands.gen:model.path', $pool, 'app/Model'))
            ->setPrefix($this->getOption('prefix', 'prefix', $pool, ''))
            ->setInheritance($this->getOption('inheritance', 'commands.gen:model.inheritance', $pool, 'Model'))
            ->setUses($this->getOption('uses', 'commands.gen:model.uses', $pool, 'Hyperf\DbConnection\Model\Model'))
            ->setForceCasts($this->getOption('force-casts', 'commands.gen:model.force_casts', $pool, false))
            ->setRefreshFillable($this->getOption('refresh-fillable', 'commands.gen:model.refresh_fillable', $pool, false))
            ->setTableMapping($this->getOption('table-mapping', 'commands.gen:model.table_mapping', $pool, []))
            ->setIgnoreTables($this->getOption('ignore-tables', 'commands.gen:model.ignore_tables', $pool, []))
            ->setWithComments($this->getOption('with-comments', 'commands.gen:model.with_comments', $pool, false))
            ->setVisitors($this->getOption('visitors', 'commands.gen:model.visitors', $pool, []))
            ->setPropertyCase($this->getOption('property-case', 'commands.gen:model.property_case', $pool));
        return $this->options;
    }

    /**
     * describe 加载表字段信息
     * author derick
     * date 2020/3/28
     * @param string $tableName
     * @return bool
     */
    private function initColumnsSchema(string $tableName)
    {
        $connection = $this->getSchemaBuilder($this->options->getPool())->getConnection();
        $result = $connection->selectFromWriteConnection("SELECT * FROM information_schema.columns WHERE `table_schema` = ? AND `table_name` = ? ORDER BY ORDINAL_POSITION", [$connection->getDatabaseName(), $tableName]);
        foreach ($result as $r) {
            $pk = strtoupper($r->COLUMN_KEY) == 'PRI' ? true : false;
            $this->columns[] = [
                'name' => $r->COLUMN_NAME,
                'dataType' => $r->DATA_TYPE,
                'defaultValue' => $r->COLUMN_DEFAULT,
                'isNullAble' => strtoupper($r->IS_NULLABLE) == 'YES' ? false : true,
                'maxLength' => $r->CHARACTER_MAXIMUM_LENGTH,
                'isPrimaryKey' => $pk,
                'pkAutoIncrement' => strtoupper($r->EXTRA) == 'AUTO_INCREMENT' ? true : false,
                'comment' => $r->COLUMN_COMMENT,
                'unsigned' => strpos(strtoupper($r->COLUMN_TYPE), 'UNSIGNED') !== false ? true : false
            ];
            if ($pk) {
                $this->pkColumn = $r->COLUMN_NAME;
            }
        }
        return true;
    }

    /**
     * describe 根据文件路径创建目录
     * author derick
     * date 2020/3/28
     * @param string $filePath
     */
    private function createFolder(string $filePath)
    {
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }
}