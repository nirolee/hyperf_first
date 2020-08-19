<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Cases;

use App\Constants\ErrorCode;
use HyperfTest\HttpTestCase;

/**
 * @internal
 * @coversNothing
 */
class UserTest extends HttpTestCase
{

    /**
     * describe: 测试接口 获取会员协议接口
     * author: derick
     * date: 2019/12/24
     */
    public function testUserAgreement() {
        $res = $this->client->get('/v1/user/agreement', [], ['ys-token' => 'wVf38L5wlY02Ab6zQhzuVnIMfjmm3FyaSAmSNKccqCHFlCAGx+S7rLNfKx4rE9FiR2XT9CQwpSa+WcQkSq9b5mlTDZrWIB1M4oYbYXl0BoDOo7/eU9cYV/HCamjdwewpznt23QZHFaPFMY2n46YyrTRQzV9h9h/PIVaudh6PyYaUtcHrDAUtuVIxOX3jZECKt1wLmCyc9HdJ2PAAhRVvdGzVn71lynblcRi7r7JCOLm1frGH8iEL4I68b1b8OnrXm9HhXpVXonSlO/UJCTbCnX6lbS294mi4iLMspi/U7o7A7OmaPDXGJBLJXNOr+1kJie4gSISlCIcaclWol1ysgZopf0aCZ3Y6useRVwo2tE+lL5rWx4rZqduatcBrZMD9Au7537B3HJfgsA1ZejrhrapwhCIn8XYXhBlthWrna/u5xAsyVSfLtwFCS/TyRbnPR2LpzEEAZ4dELBR6hpef5E4/QCSUHzL8I6ZArXLGkGtwH+3N0MQrU65RpjVBF01+jVejf2IzRyEZui136IekTibzv99tnmCbkMhVUFyh4qhcRyBwxsCU9QifF5e58Y76KwcAMR/ea87BedGA3tcD5feNQwFs5pUURPz0KuRKzOi3caN+qY9OfkqBJoOJ1XJWJdBuVwNJbhwqXm9Q3pnx/LAMFQAv9HTwmatmsF9X3Ko=']);
        $this->assertSame(ErrorCode::REQUEST_SUCCESS, $res['code']);
        $this->assertIsString($res['data']);
    }

    /**
     * describe:测试接口 我的代金券列表
     * author: derick
     * date: 2019/12/24
     */
    public function testUserMyCashCoupon() {
        $res = $this->client->get('/v1/user/my-cashcoupon', [
            'page' => 1,
            'limit' => 15,
            'status' => 10
        ], ['ys-token' => 'wVf38L5wlY02Ab6zQhzuVnIMfjmm3FyaSAmSNKccqCHFlCAGx+S7rLNfKx4rE9FiR2XT9CQwpSa+WcQkSq9b5mlTDZrWIB1M4oYbYXl0BoDOo7/eU9cYV/HCamjdwewpznt23QZHFaPFMY2n46YyrTRQzV9h9h/PIVaudh6PyYaUtcHrDAUtuVIxOX3jZECKt1wLmCyc9HdJ2PAAhRVvdGzVn71lynblcRi7r7JCOLm1frGH8iEL4I68b1b8OnrXm9HhXpVXonSlO/UJCTbCnX6lbS294mi4iLMspi/U7o7A7OmaPDXGJBLJXNOr+1kJie4gSISlCIcaclWol1ysgZopf0aCZ3Y6useRVwo2tE+lL5rWx4rZqduatcBrZMD9Au7537B3HJfgsA1ZejrhrapwhCIn8XYXhBlthWrna/u5xAsyVSfLtwFCS/TyRbnPR2LpzEEAZ4dELBR6hpef5E4/QCSUHzL8I6ZArXLGkGtwH+3N0MQrU65RpjVBF01+jVejf2IzRyEZui136IekTibzv99tnmCbkMhVUFyh4qhcRyBwxsCU9QifF5e58Y76KwcAMR/ea87BedGA3tcD5feNQwFs5pUURPz0KuRKzOi3caN+qY9OfkqBJoOJ1XJWJdBuVwNJbhwqXm9Q3pnx/LAMFQAv9HTwmatmsF9X3Ko=']);
        $this->assertSame(ErrorCode::REQUEST_SUCCESS, $res['code']);
        $this->assertIsArray($res['data']);
    }

    /**
     * describe:测试接口 获取用户星购卡
     * author: derick
     * date: 2019/12/24
     */
    public function testUserMyStoredCard() {
        $res = $this->client->get('/v1/user/my-stored-card', [], ['ys-token' => 'wVf38L5wlY02Ab6zQhzuVnIMfjmm3FyaSAmSNKccqCHFlCAGx+S7rLNfKx4rE9FiR2XT9CQwpSa+WcQkSq9b5mlTDZrWIB1M4oYbYXl0BoDOo7/eU9cYV/HCamjdwewpznt23QZHFaPFMY2n46YyrTRQzV9h9h/PIVaudh6PyYaUtcHrDAUtuVIxOX3jZECKt1wLmCyc9HdJ2PAAhRVvdGzVn71lynblcRi7r7JCOLm1frGH8iEL4I68b1b8OnrXm9HhXpVXonSlO/UJCTbCnX6lbS294mi4iLMspi/U7o7A7OmaPDXGJBLJXNOr+1kJie4gSISlCIcaclWol1ysgZopf0aCZ3Y6useRVwo2tE+lL5rWx4rZqduatcBrZMD9Au7537B3HJfgsA1ZejrhrapwhCIn8XYXhBlthWrna/u5xAsyVSfLtwFCS/TyRbnPR2LpzEEAZ4dELBR6hpef5E4/QCSUHzL8I6ZArXLGkGtwH+3N0MQrU65RpjVBF01+jVejf2IzRyEZui136IekTibzv99tnmCbkMhVUFyh4qhcRyBwxsCU9QifF5e58Y76KwcAMR/ea87BedGA3tcD5feNQwFs5pUURPz0KuRKzOi3caN+qY9OfkqBJoOJ1XJWJdBuVwNJbhwqXm9Q3pnx/LAMFQAv9HTwmatmsF9X3Ko=']);
        $this->assertSame(ErrorCode::REQUEST_SUCCESS, $res['code']);
        $this->assertIsArray($res['data']);
    }

    /**
     * describe:测试接口 我的优惠券列表
     * author: derick
     * date: 2019/12/24
     */
    public function testUserMyCoupon() {
        $res = $this->client->get('/v1/user/my-coupon', [
            'page' => 1,
            'limit' => 15,
            'display' => 'all'
        ], ['ys-token' => 'wVf38L5wlY02Ab6zQhzuVnIMfjmm3FyaSAmSNKccqCHFlCAGx+S7rLNfKx4rE9FiR2XT9CQwpSa+WcQkSq9b5mlTDZrWIB1M4oYbYXl0BoDOo7/eU9cYV/HCamjdwewpznt23QZHFaPFMY2n46YyrTRQzV9h9h/PIVaudh6PyYaUtcHrDAUtuVIxOX3jZECKt1wLmCyc9HdJ2PAAhRVvdGzVn71lynblcRi7r7JCOLm1frGH8iEL4I68b1b8OnrXm9HhXpVXonSlO/UJCTbCnX6lbS294mi4iLMspi/U7o7A7OmaPDXGJBLJXNOr+1kJie4gSISlCIcaclWol1ysgZopf0aCZ3Y6useRVwo2tE+lL5rWx4rZqduatcBrZMD9Au7537B3HJfgsA1ZejrhrapwhCIn8XYXhBlthWrna/u5xAsyVSfLtwFCS/TyRbnPR2LpzEEAZ4dELBR6hpef5E4/QCSUHzL8I6ZArXLGkGtwH+3N0MQrU65RpjVBF01+jVejf2IzRyEZui136IekTibzv99tnmCbkMhVUFyh4qhcRyBwxsCU9QifF5e58Y76KwcAMR/ea87BedGA3tcD5feNQwFs5pUURPz0KuRKzOi3caN+qY9OfkqBJoOJ1XJWJdBuVwNJbhwqXm9Q3pnx/LAMFQAv9HTwmatmsF9X3Ko=']);
        $this->assertSame(ErrorCode::REQUEST_SUCCESS, $res['code']);
        $this->assertIsArray($res['data']);
    }

}
