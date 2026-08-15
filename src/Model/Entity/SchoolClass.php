<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * School class entity.
 *
 * `Class` became a reserved name in PHP 7, so the Classes table explicitly
 * uses this entity name instead of CakePHP's default singular form.
 */
class SchoolClass extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'school_id' => true,
        'class_id' => true,
        'course_name' => true,
        'section_name' => true,
        'period_name' => true,
        'staff_id' => true,
        'school' => true,
        'classes' => true,
        'staff' => true,
        'rosters' => true,
    ];
}
