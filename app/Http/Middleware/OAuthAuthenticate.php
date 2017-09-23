<?php

namespace App\Http\Middleware;

use Closure;
use Event;
use Stoneworld\Wechat\Auth;
use App\Models\Employee;

/**
 * Class OAuthAuthenticate.
 */
class OAuthAuthenticate
{
    /**
     * Use Service Container would be much artisan.
     */
    private $oauth;

    /**
     * Inject the wechat service.
     */
    public function __construct()
    {
    	$appId  = config("ent.appId");
    	$secret = config("ent.secret");
        $this->oauth = new Auth($appId,$secret);
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param string|null              $guard
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
    		
       //$isNewSession = false;
    	$user = config('ent.mock_user');
    	 
    	if (!empty($user) && config('ent.enable_mock') ) {

    		$user = Employee::where("userid",$user)->first();
    		session(['wechat.oauth_user' => $user]);
    		
    	}

        if (!session('wechat.oauth_user')) {
         
        	$user = $this->oauth->authorize($to = null, $state = 'STATE', $usertype = 'member');
        	if(!isset($user["UserId"])){
        	  return 	redirect()->route("msg",["type"=>"error","title"=>"用户未关注","content"=>"您还未关注企业号，请先关注，并通过企业员工认证，如遇到问题请联系管理员"]);
        	}
        	$user = Employee::where("userid",$user["UserId"])->first();
        	if(!isset($user)){
        		return redirect()->route("msg",["type"=>"error","title"=>"用户未认证","content"=>"您还未通过企业认证，请先认证后再使用系统，如遇到问题请联系管理员"]);
        	}
            session(['wechat.oauth_user' =>  $user ]);
           
        }

        //Event::fire(new WeChatUserAuthorized(session('wechat.oauth_user'), $isNewSession));
        return $next($request);
    }

    /**
     * Build the target business url.
     *
     * @param Request $request
     *
     * @return string
     */
    public function getTargetUrl($request)
    {
        $queries = array_except($request->query(), ['code', 'state']);

        return $request->url().(empty($queries) ? '' : '?'.http_build_query($queries));
    }
}
