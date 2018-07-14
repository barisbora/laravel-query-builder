<?php

namespace App;

trait QueryBuilder
{

    public static function build( $request = null )
    {
        return new QueryBuilderHandler( self::query(), $request ?? request() );
    }

}
