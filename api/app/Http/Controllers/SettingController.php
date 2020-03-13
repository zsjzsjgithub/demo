<?php
/**
 * 网站管理
 */

namespace App\Http\Controllers;

use App\Models\FhConfig;
use App\Pop;
use App\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SettingController extends Controller
{
    private $admin;

    /**
     * SettingController constructor.
     *
     * @throws AuthenticationException
     */
    public function __construct()
    {
        if (\Illuminate\Support\Facades\Request::path() !== 'v1/settings/pops') {
            $this->admin = allow(User::TYPE_ADMIN);
        }
    }

    public function index()
    {
        $config = fhget();
        $base = [
            'rate_1' => $config['trade']['rate'][1] ?? 0,
            'rate_2' => $config['trade']['rate'][2] ?? 0,
            'rate_3' => $config['trade']['rate'][3] ?? 0,
            'price_1' => $config['trade']['prices'][0] ?? 0,
            'price_2' => $config['trade']['prices'][1] ?? 0,
            'price_3' => $config['trade']['prices'][2] ?? 0,
            'price_4' => $config['trade']['prices'][3] ?? 0,
            'price_5' => $config['trade']['prices'][4] ?? 0,
            'price_6' => $config['trade']['prices'][5] ?? 0,
            'first_rate' => $config['account']['first_rate'] ?? 0,
            'range' => $config['trade']['range'] ?? 0,
            'allow_register' => $config['allow_register'] ?? true,
        ];

        $ips = $config['admin_ips'] ?? [];

        $logs = $this->admin->logs()->orderByDesc('time')->limit(10)->get();

        return compact('base', 'ips', 'logs');
    }

    /**
     * @param Request $request
     *
     * @throws ValidationException
     *
     */
    public function update(Request $request)
    {
        $this->verify($request, [
            'rate_1' => 'required|numeric',
            'rate_2' => 'required|numeric',
            'rate_3' => 'required|numeric',
            'range' => 'required|numeric',
            'price_1' => 'required|numeric',
            'price_2' => 'required|numeric',
            'price_3' => 'required|numeric',
            'price_4' => 'required|numeric',
            'price_5' => 'required|numeric',
            'price_6' => 'required|numeric',
            'first_rate' => 'required|numeric',
            'allow_register' => 'required|boolean',
        ]);

        $trade = [
            'rate' => [
                1 => (float) $request->input('rate_1'),
                2 => (float) $request->input('rate_2'),
                3 => (float) $request->input('rate_3'),
            ],
            'prices' => [
                (float) $request->input('price_1'),
                (float) $request->input('price_2'),
                (float) $request->input('price_3'),
                (float) $request->input('price_4'),
                (float) $request->input('price_5'),
                (float) $request->input('price_6'),
            ],
            'range' => (float) $request->input('range'),
        ];
        FhConfig::set('trade', $trade);

        $account = [
            'first_rate' => (float) $request->input('first_rate'),
        ];
        FhConfig::set('account', $account);

        FhConfig::set('allow_register', (bool) $request->input('allow_register'));
    }

    /**
     * @param Request $request
     *
     * @throws ValidationException
     */
    public function updateIp(Request $request)
    {
        $this->verify($request, [
            'ips' => 'required|array',
            'ips.*' => 'ip',
        ]);

        $ips = [];
        foreach ($request->input('ips') as $value) {
            $ip = str_replace(' ', '', $value);
            if ($ip) {
                $ips[] = $ip;
            }
        }

        FhConfig::set('admin_ips', $ips);
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    public function upload(Request $request)
    {
        $path = $request->file('file')->store('import');

        return Storage::url($path);
    }

    public function pops(Request $request)
    {
        if ($request->filled('is_enable')) {
            return Pop::where('is_enable', true)->get();
        }
        return Pop::all();
    }

    /**
     * @param Request $request
     *
     * @throws ValidationException
     */
    public function createPop(Request $request)
    {
        $this->verify($request, [
            'title' => 'required|max:100',
            'content' => 'required',
            'width' => 'required|integer',
            'height' => 'required|integer',
            'xt' => 'required|in:left,right,center',
            'x' => 'integer',
            'yt' => 'required|in:top,bottom,center',
            'y' => 'integer',
        ]);

        if ($request->input('xt') === 'center') {
            $x = 0;
        } else {
            if (!$x = $request->input('x')) {
                $this->error('请填写x');
            }
        }

        if ($request->input('yt') === 'center') {
            $y = 0;
        } else {
            if (!$y = $request->input('y')) {
                $this->error('请填写y');
            }
        }

        Pop::create(array_merge($request->only([
            'title',
            'content',
            'width',
            'height',
            'xt',
            'yt',
        ]), [
            'x' => $x,
            'y' => $y,
        ]));
    }

    /**
     * @param Request $request
     *
     * @param int $id
     *
     * @throws ValidationException
     */
    public function updatePop(Request $request, int $id)
    {
        $this->verify($request, [
            'title' => 'required|max:100',
            'content' => 'required',
            'width' => 'required|integer',
            'height' => 'required|integer',
            'xt' => 'required|in:left,right,center',
            'x' => 'integer',
            'yt' => 'required|in:top,bottom,center',
            'y' => 'integer',
        ]);

        $pop = Pop::findOrFail($id);

        if ($request->input('xt') === 'center') {
            $x = 0;
        } else {
            if (!$x = $request->input('x')) {
                $this->error('请填写x');
            }
        }

        if ($request->input('yt') === 'center') {
            $y = 0;
        } else {
            if (!$y = $request->input('y')) {
                $this->error('请填写y');
            }
        }

        $pop->update(array_merge($request->only([
            'title',
            'content',
            'width',
            'height',
            'xt',
            'yt',
        ]), [
            'x' => $x,
            'y' => $y,
        ]));
    }

    public function toggleEnable(Request $request, int $id)
    {
        /** @var Pop $pop */
        $pop = Pop::findOrFail($id);
        $pop->is_enable = $request->input('is_enable', false);
        $pop->save();
    }

}
