<?php

namespace App\Enums;

enum EducationLevel: string
{
    case HighSchool = 'high_school';
    case Diploma = 'diploma';
    case Associate = 'associate';
    case Bachelor = 'bachelor';
    case Master = 'master';
    case Doctorate = 'doctorate';
    case Certificate = 'certificate';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::HighSchool => 'High School',
            self::Diploma => 'Diploma',
            self::Associate => 'Associate Degree',
            self::Bachelor => "Bachelor's Degree",
            self::Master => "Master's Degree",
            self::Doctorate => 'Doctorate',
            self::Certificate => 'Professional Certificate',
            self::Other => 'Other',
        };
    }
}
