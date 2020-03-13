<?php

namespace App\Exports;

use App\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MemberExport implements FromCollection, WithHeadings
{
    public function headings(): array
    {
        return [
            '#',
            '账号',
            '姓名',
            '伙伴账号',
            '伙伴名称',
            '银行名称',
            '银行卡号',
            '存款总额',
            '取款总额',
            '利润',
            '余额',
            '注册时间',
            '登录时间',
            '状态',
        ];
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $members = User::with('agent')
            ->where('type', User::TYPE_MEMBER)
            ->latest()
            ->get();

        $data = collect();
        $members->each(function (User $member) use (&$data) {
            $agent = $member->agent;
            $data->push([
                $member->id,
                $member->username,
                $member->nickname,
                $agent ? $agent->username : '',
                $agent ? $agent->nickname : '',
                $member->bank_name,
                $member->bank_number,
                $member->deposit,
                $member->withdrawal,
                $member->deposit - $member->withdrawal,
                $member->balance,
                $member->created_at,
                $member->logged_at,
                $member->is_enabled ? '启用' : '禁用',
            ]);
        });
        return $data;
    }
}
