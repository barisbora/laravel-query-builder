<?php

namespace BarisBora\QueryBuilder;

trait QueryBuilder
{

    public static function builder( $request = null )
    {
        return new QueryBuilderHandler( self::query(), $request ?? request() );
    }

    public function loader( $request = null )
    {
        return new ModelBuilderHandler( $this, $request );
    }

}
