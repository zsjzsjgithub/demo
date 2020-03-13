<?php
return [
    'error' => '字段错误',
    'required' => '请填写:attribute',
    'unique' => '该:attribute已存在',
    'max' => [
        'numeric' => ':attribute不能大于:max',
        'string' => ':attribute不能超过:max个字符',
        'array' => ':attribute的最大个数为:max个',
    ],
    'min' => [
        'numeric' => ':attribute不能小于:min',
        'string' => ':attribute不能少于:min个字符',
        'array' => ':attribute的最大个数为:min个',
    ],
    'confirmed' => '两次输入:attribute不正确',
    'exists' => '选择的:attribute不存在',
    'filled' => ':attribute不能为空',
    'ip' => ':attribute必须是一个合法的IP地址',

    // 字段
    'attributes' => [
        'username' => '账号',
        'nickname' => '姓名',
        'old_password' => '旧密码',
        'password' => '密码',
        'password_confirmation' => '确认的密码',
        'tel' => '手机号码',
        'bank_name' => '银行名称',
        'bank_number' => '银行账号',
        'agent_name' => '代理人账号',
        'amount' => '金额',
        'ips.*' => '管理员IP'
    ],

    // 自定义消息
    'custom' => [
        'username.regex' => ':attribute不合法，必须以小写字母开头且为数字、下划线或者小写字母的组合',
    ],
];
