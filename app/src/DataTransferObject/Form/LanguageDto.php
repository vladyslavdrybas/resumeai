<?php
declare(strict_types=1);

namespace App\DataTransferObject\Form;

use App\DataTransferObject\IDataTransferObject;

class LanguageDto implements IDataTransferObject
{
    public function __construct(
       public ?string $title = null,
       public ?string $level = 'a1',
       public ?string $code = null
    ) {}
}
