<?php
namespace App\Presenters;

class ResponsePresenter
{
    public function Response($array)
    {
        $error = false;
        if($array['error'] != NULL)
            $error = true;

        if($array['error'] == NULL)
            unset($array['error']);

        if($array['data'] == NULL) {  // error exist
            unset($array['data']);
            $error = true;
        }
        if($error == true)
            return response()->json($array,400);
        else
            return $array;
    }
}
