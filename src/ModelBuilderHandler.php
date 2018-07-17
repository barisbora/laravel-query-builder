<?php

namespace BarisBora\QueryBuilder;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ModelBuilderHandler
{

    /**
     * @var Model
     */
    private $model;

    /**
     * @var \Illuminate\Http\Request
     */
    private $request;

    public function __construct( Model $model, $request )
    {

        $this->request = $request ?? request();

        $this->model = $model;

    }

    /**
     * @param      $property
     * @param bool $scope
     * @return $this
     */
    public function allowFilter( $property, bool $scope = false )
    {

        $filters = (array) $this->request->get( 'filter' );

        if ( isset( $filters[ $property ] ) ) {

            $value = $filters[ $property ];

            $relations = explode( '.', $property );

            $property = array_splice( $relations, count( $relations ) - 1 )[ 0 ];

            $this->cascadeDownRelations( $relations, $property, $scope, $value );

        }

        return $this;

    }

    /**
     * @param array $includes
     * @return $this
     */
    public function allowedIncludes( array $includes )
    {

        $includes = collect( $includes )->transform( function ( $include ) {

            $incs = [];

            $explode = array_reverse( explode( '.', $include ) );

            foreach ( $explode as $key => $item ) {
                $incs[] = implode( '.', array_reverse( array_slice( $explode, $key, null, true ) ) );
            }

            return $incs;

        } )->unique()->flatten()->values();

        $getIncludes = collect( explode( ',', $this->request->get( 'include' ) ) )->unique()->values();

        $this->with( $getIncludes->intersect( $includes )->toArray() );

        return $this;

    }

    public function allowedLoads( array $loads )
    {

        $includes = [];

        foreach ( $loads as $key => $load ) {

            $column = $key;
            $callable = $load;

            if ( is_int( $key ) ) {
                $column = $load;
                $callable = null;
            }

            $explode = array_reverse( explode( '.', $column ) );

            foreach ( $explode as $base => $item ) {
                $includes[] = implode( '.', array_reverse( array_slice( $explode, $base, null, true ) ) );
            }

            continue;

        }

        $includes = array_values( array_unique( array_flatten( $includes ) ) );

        #
        $getIncludes = collect( explode( ',', $this->request->get( 'include' ) ) )->unique()->values();

        $intersections = $getIncludes->intersect( $includes )->unique()->values();

        foreach ( $intersections as $intersection ) {

            if ( isset( $loads[ $intersection ] ) ) {
                $this->model->load( [
                    $intersection => function ( $query ) use ($loads, $intersection) {
                        $loads[ $intersection ]( $query );
                    },
                ] );

                continue;
            }

            $this->model->load( $intersection );

        }

        return $this->model;

    }
}
