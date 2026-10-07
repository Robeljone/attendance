<?php

namespace App\Enums;

enum EmployeeDocumentType: string
{
    case NationalId = 'national_id';
    case Passport = 'passport';
    case WorkPermit = 'work_permit';
    case Contract = 'contract';
    case Resume = 'resume';
    case Diploma = 'diploma';
    case Certificate = 'certificate';
    case Medical = 'medical';
    case Tax = 'tax';
    case Bank = 'bank';
    case PhotoId = 'photo_id';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::NationalId => 'National ID',
            self::Passport => 'Passport',
            self::WorkPermit => 'Work Permit',
            self::Contract => 'Employment Contract',
            self::Resume => 'Resume / CV',
            self::Diploma => 'Diploma / Degree',
            self::Certificate => 'Certificate',
            self::Medical => 'Medical Record',
            self::Tax => 'Tax Document',
            self::Bank => 'Bank Document',
            self::PhotoId => 'Photo ID',
            self::Other => 'Other',
        };
    }
}
