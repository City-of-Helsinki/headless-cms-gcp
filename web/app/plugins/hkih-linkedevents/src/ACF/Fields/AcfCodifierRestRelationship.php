<?php
/**
 * Acf codifier relationship field
 */

namespace HKIH\LinkedEvents\ACF\Fields;

use Geniem\ACF\Field\Common\MinMax;

/**
 * Class AcfCodifierRestRelationship
 */
class AcfCodifierRestRelationship extends \Geniem\ACF\Field {

    use MinMax;

    /**
     * Return format
     *
     * @var string
     */
    protected $return_format;

    /**
     * Field type
     *
     * @var string
     */
    protected $type = 'rest_relationship';

    /**
     * Export field in ACF's native format.
     *
     * @param boolean $register Whether the field is to be registered.
     * @param mixed   $parent   Possible parent object.
     *
     * @return array
     * @throws \Geniem\ACF\Exception Throws an exception if a key or a name is not defined.
     */
    public function export( bool $register = false, $parent = null ) : ?array {
        $obj = parent::export( $register, $parent );

        if ( isset( $obj['field_filters'] ) ) {
            $obj['filters'] = $obj['field_filters'];
            unset( $obj['field_filters'] );
        }

        return $obj;
    }

    /**
     * Register a relationship query filtering function for the field
     *
     * @param callable $function A function to register.
     *
     * @return self
     */
    public function relationship_query( callable $function ) : AcfCodifierRestRelationship {
        $this->filters['rest_relationship_query'] = [
            'filter'        => 'acf/fields/rest_relationship/query/key=',
            'function'      => $function,
            'priority'      => 10,
            'accepted_args' => 3,
        ];

        return $this;
    }

    /**
     * Register a relationship result filtering function for the field
     *
     * @param callable $function A function to register.
     *
     * @return self
     */
    public function relationship_result( callable $function ) : AcfCodifierRestRelationship {
        $this->filters['rest_relationship_result'] = [
            'filter'        => 'acf/fields/rest_relationship/result/key=',
            'function'      => $function,
            'priority'      => 10,
            'accepted_args' => 4,
        ];

        return $this;
    }

    /**
     * Update value filter override.
     *
     * This lets us access 4th argument, the raw values.
     *
     * @param callable $function Filter.
     * @param int      $priority Priority.
     *
     * @return $this|\HKIH\LinkedEvents\ACF\Fields\AcfCodifierRestRelationship
     */
    public function update_value( callable $function, int $priority = 10 ) : self {
        $this->filters['update_value'] = [
            'filter'        => 'acf/update_value/key=',
            'function'      => $function,
            'priority'      => $priority,
            'accepted_args' => 4,
        ];

        return $this;
    }
}
