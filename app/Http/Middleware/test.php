<?php

namespace App\Http\Middleware;
use DB;
use Closure;
use Storage;
class test
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
        $IfExist = DB::table('products_photos')->where('filename','=',$request->file_name)->first();
        if(!$IfExist) {
            //Storage::disk('s3')->download($request->file_name);
            return response()->json($request->file_name." doesn't exist");
        }

        return $next($request);

    }
}
