<?php
declare(strict_types=1);

namespace App\Admin\Library;

use App\Admin\Constants\BusinessConst;
use App\Admin\Constants\ErrorCode;
use App\Common\Library\AbstractController;
use Hyperf\Contract\SessionInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\Arr;
use Hyperf\View\RenderInterface;
use Psr\Http\Message\ResponseInterface;

class MyController extends AbstractController
{
    /**
     * @Inject()
     * @var RenderInterface
     */
    protected $render;

    /**
     * @Inject()
     * @var SessionInterface
     */
    protected $session;

    /**
     * describe: 渲染模板
     * author: derick
     * date: 2020/1/15
     * @param string $template 模板名称
     * @param array $data 模板变量
     * @param bool $return 是否返回模板字符串
     * @return mixed
     */
    public function render(string $template, Array $data = [], bool $return = false)
    {
        if (strpos($template, '/') === false) {
            $fullControllerName = class_basename(get_class($this));
            $pos = strpos($fullControllerName, 'Controller');
            if ($pos !== false) {
                $controllerName = substr($fullControllerName, 0, $pos);
                $template = strtolower($controllerName) . '/' . $template;
            }
        }
        if ($return) {
            return $this->render->getContents($template, $this->mergeLayoutVar($data));
        } else {
            return $this->render->render($template, $this->mergeLayoutVar($data));
        }
    }

    /**
     * describe 返回异步请求数据
     * author derick
     * date 2020/3/8
     * @param int $code 接口返回状态码
     * @param bool $status 接口返回操作状态
     * @param array $arr 返回参数
     * @return ResponseInterface
     */
    public function json(int $code = ErrorCode::REQUEST_SUCCESS, bool $status = null, Array $arr = []): ResponseInterface
    {
        $return = [
            'code' => $code,
            'msg' => trans(ErrorCode::getMessage($code)),
            'status' => is_null($status) ? $code == ErrorCode::REQUEST_SUCCESS : $status,
        ];
        if (isset($arr['data'])) {
            $return = array_merge($return, $arr);
        } else {
            $return['data'] = $arr;
        }
        return $this->response->json($return);
    }

    /**
     * describe 判断当前请求是否是post请求
     * author derick
     * date 2020/3/10
     * @return bool
     */
    public function isPost(): bool
    {
        return $this->request->isMethod('POST');
    }

    /**
     * describe 判断当前请求是否是ajax请求
     * author derick
     * date 2020/3/10
     * @return bool
     */
    public function isAjax(): bool
    {
        $ajaxHeader = $this->request->getHeaderLine('X-Requested-With');
        return strtolower($ajaxHeader) == 'xmlhttprequest' ? true : false;
    }

    /**
     * describe 合并布局文件公共变量
     * author derick
     * date 2020/3/12
     * @param array $data
     * @return array
     */
    private function mergeLayoutVar(array &$data): array
    {
        // 读取左侧菜单
        $data['navMenuList'] = $this->session->get(BusinessConst::ADMIN_MENU_LIST_KEY, []);
        $data['adminInfo'] = $this->session->get(BusinessConst::ADMIN_SESSION_KEY, []);
        $trees = $this->session->get(BusinessConst::ADMIN_MENU_TREE_KEY, []);
        $currentPath = $this->request->getUri()->getPath();
        $data['focusMenu'] = isset($trees[$currentPath]) ? $trees[$currentPath]['parentUrl'] : $currentPath;
        $breadcrumb = [];
        if (isset($trees[$currentPath])) {
            $breadcrumb[] = ['url' => $trees[$currentPath]['url'], 'name' => $trees[$currentPath]['name']];
            $currentPath = $trees[$currentPath]['parentId'];
        } else {
            foreach ($trees as $t) {
                if ($t['url'] == $currentPath) {
                    $breadcrumb[] = ['url' => $t['url'], 'name' => $t['name']];
                    $currentPath = $t['parentId'];
                    break;
                }
            }
        }
        while (true) {
            if (isset($trees[$currentPath])) {
                array_unshift($breadcrumb, ['url' => $trees[$currentPath]['url'], 'name' => $trees[$currentPath]['name']]);
                $currentPath = $trees[$currentPath]['parentId'];
                if ($currentPath == 0) {
                    break;
                }
                continue;
            }
            break;
        }
        array_unshift($breadcrumb,['name' => trans('message.homePage'), 'url' => '/']);
        $data['breadcrumb'] = $breadcrumb;
        return $data;
    }
}