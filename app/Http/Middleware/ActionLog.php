<?php

namespace App\Http\Middleware;

use Auth, Closure;

use App\Log;

class ActionLog
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ( !Auth::check() ) return $next($request);

        Log::create([
            'method'    => $request->method(),
            'userId'    => Auth::user()->id,
            'referer'   => $request->server('HTTP_REFERER'),
            'ip'        => $request->server('REMOTE_ADDR'),
            'agent'     => $request->server('HTTP_USER_AGENT'),
            'query'     => json_encode( $request->query() ),
            'request'   => json_encode( $request->all() ),
        ]);

        return $next($request);
    }
}
