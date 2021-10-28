<?php
namespace Civi\Api4;

/**
 * Resource entity.
 *
 * Provided by the CiviCRM Resource Management extension.
 *
 * @package Civi\Api4
 */
class Resource extends Generic\DAOEntity {

    /**
     * Checks whether this resource meets the presented resource demand
     * @param bool $checkPermissions
     * @return Action\Resource\MeetsDemand
     */
    public static function meets_demand($checkPermissions = TRUE) {
        return (new Action\Resource\MeetsDemand(__CLASS__, __FUNCTION__))
            ->setCheckPermissions($checkPermissions);
    }
}
