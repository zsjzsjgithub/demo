<?php
use App\Message;
use App\Order;

return [
    'error' => [
        'unauthenticated' => '没有权限',
    ],

    'token' => [
        'login_error' => '账号或者密码错误',
        'user_disabled' => '账号已禁用',
        'exited' => '已退出',
        'old_password_error' => '旧密码错误',
        'user_exists' => '用户名已存在，请重新输入',
        'agent_noexists' => '伙伴不存在',
        'agent_empty' => '伙伴为空',
    ],

    'account' => [
        'type' => [
            1 => '存款',
            2 => '取款',
            3 => '购买',
            4 => '收入',
            5 => '系统调整',
        ],
        'status' => [
            1 => '待审核',
            2 => '已驳回',
            3 => '已完成',
            4 => '等待中',
        ],
        'amount_error' => '金额必须被1000整除',
        'amount_balance' => '余额不足',
    ],
    'trade' => [
        'scene_error' => '无效的场次',
        'balance_error' => '您的余额不足',
        'repeat_error' => '不能购买同一场次',
    ],
    'order' => [
        'type' => [
            Order::TYPE_BUY => 'BUY',
            Order::TYPE_SELL => 'SELL',
            Order::TYPE_CANCEL => 'CANCEL',
        ],
        'status' => [
            Order::STATUS_PENDING => '进行中',
            Order::STATUS_WIN => '赢得',
            Order::STATUS_LOSE => '失去',
        ],
    ],
    'message' => [
        'type' => [
            Message::TYPE_SERVICE => '1对1客服',
            Message::TYPE_PROBLEM => '常见问题',
            Message::TYPE_NOTICE => '通知',
        ],
        'question_error' => '提问过于频繁，请稍后再试！',
    ],
    'statistic' => [
        'sum' => '小计',
        'total' => '合计',
    ],
    'data' => [
        'password_error' => '密码错误',
    ],
];
