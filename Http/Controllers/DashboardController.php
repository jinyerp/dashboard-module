<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Auth;

use Jiny\Table\Http\Controllers\BaseController;

class DashboardController extends BaseController
{
    use \Jiny\Table\Http\Livewire\Permit;
    use \Jiny\Table\Http\Controllers\SetMenu;

    protected function checkRequestNesteds($request)
    {
        if (isset($this->actions['nesteds'])) {
            foreach($this->actions['nesteds'] as $i => $nested) {
                if(isset($request->$nested)) {
                    unset($this->actions['nesteds'][$i]);
                    $this->actions['nesteds'][$nested] = $request->$nested;
                    $this->actions['request']['nesteds'][$nested] = $request->$nested;
                }
            }
        }

        return $this;
    }

    // Request에서 전달된 query 스트링값을 저장합니다.
    protected function checkRequestQuery($request)
    {
        if($request->query) {
            foreach($request->query as $key => $q) {
                $this->actions['request']['query'][$key] = $q;
            }
        }
        return $this;
    }

    /**
     * 데시보드 화면을 출력합니다.
     */
    public function index(Request $request)
    {
        $this->checkRequestNesteds($request);
        $this->checkRequestQuery($request);


        // 사용자 메뉴 설정
        $user = Auth::user();
        if($user) {
            $this->setUserMenu($user);
        }

        // 권한
        $this->permitCheck();
        if($this->permit['read']) {

            // 사용자 정의 페이지
            if (isset($this->actions['view_main'])) {

                if (view()->exists($this->actions['view_main']))
                {
                    $viewfile = $this->actions['view_main'];
                } else {
                    return "지정한 사용자 blade main 파일이 없습니다.";
                }

            } else {
                $viewfile = "dashboard::index";
            }

            return view($viewfile,[
                'actions'=>$this->actions,
                'request'=>$request
            ]);

        }


        // 권한 접속 없음
        return view("dashboard::error.permit",[
            'actions'=>$this->actions,
            'request'=>$request
        ]);
    }


    public function setViewMain($blade)
    {
        $this->actions['view_main'] = $blade;
        return $this;
    }

}
