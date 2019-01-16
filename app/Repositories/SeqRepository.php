<?php
namespace App\Repositories;

use App\File;
use Illuminate\Http\Request;


class SeqRepository
{
    public function Generate_seq($select,$query)
    {
        $link = mysqli_connect('team-dev-mysqldb.cxkbhs9nvapa.ap-southeast-1.rds.amazonaws.com','dev-db-admin','55zCPN52UhrcmkEhH5wtFahbTv2FS8Nv','seq-db');
        $sql = $select.' '.$query;
        $result = mysqli_query($link,$sql)->fetch_array(MYSQLI_ASSOC);
        return $result[$query];
    }
};

