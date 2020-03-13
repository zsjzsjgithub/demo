<?php
use App\Message;
use App\Order;

return [
    'error' => [
        'unauthenticated' => '没有权限',
    ],

    'token' => [
        'login_error' => '아이디/비밀번호가 잘못되엇습니다',
        'user_disabled' => '로그인불가능한 아이디입니다',
        'exited' => '已退出',
        'old_password_error' => '비번이 잘못되엇습니다 ',
        'user_exists' => '필수 정보입니다.',
        'agent_noexists' => '사용 불가능',
        'agent_empty' => '필수 정보입니다.',
    ],

    'account' => [
        'type' => [
            1 => '입금',
            2 => '환전',
            3 => '구매',
            4 => '수익',
            5 => '관리자',
        ],
        'status' => [
            1 => '확인중',
            2 => '취소',
            3 => '완료',
            4 => '대기중',
        ],
        'amount_error' => '1000단위로만 입력가능합니다',
        'amount_balance' => '잔액이 부족합니다',
    ],
    'trade' => [
        'scene_error' => '无效的场次',
        'balance_error' => '잔액이 부족합니다',
        'repeat_error' => '不能购买同一场次',
    ],
    'order' => [
        'type' => [
            Order::TYPE_BUY => 'BUY',
            Order::TYPE_SELL => 'SELL',
            Order::TYPE_CANCEL => 'CANCEL',
        ],
        'status' => [
            Order::STATUS_PENDING => '진행중',
            Order::STATUS_WIN => '수익',
            Order::STATUS_LOSE => '손실',
        ],
    ],
    'message' => [
        'type' => [
            Message::TYPE_SERVICE => '1:1문의',
            Message::TYPE_PROBLEM => '자주하는 질문',
            Message::TYPE_NOTICE => '공지사항',
        ],
        'question_error' => '문의는 10초에 1번 만 가능합니다',
    ],
    'statistic' => [
        'sum' => '小计',
        'total' => '合计',
    ],
    'data' => [
        'password_error' => '密码错误',
    ],
];
