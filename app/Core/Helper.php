<?php

namespace App\Core;

class Helper
{
    public static function GenerateSrNo($page = 1, $rpp = 10)
    {
        $page = (int) $page;

		$rpp = (int) $rpp;
		
        $page = $page ? $page-1 : 0;

        $rpp = $rpp ? 10 : $rpp;

        return ($page*$rpp)+1;
    }

    public static function pagination($total, $page = 1, $limit = 50)
    {
        $totalPages = ceil( $total/ $limit ); //calculate total pages

        $page = max($page, 1); //get 1 page when $_GET['page'] <= 0

        $page = min($page, $totalPages); //get last page when $_GET['page'] > $totalPages

        $offset = ($page - 1) * $limit;
        
        if( $offset < 0 ) $offset = 0;

        return $totalPages;
    }
}