<?php

declare(strict_types=1);

namespace App\Entity;

enum ModelEnum: string
{
    case ENBEDDRUS = 'enbeddrus';
    case ARCTIC2 = 'arctic2';
    case BGE2 = 'bgem2';

    public function modelName(): string
    {
        return match ($this) {
            self::ENBEDDRUS => 'evilfreelancer/enbeddrus',
            self::ARCTIC2 => 'snowflake-arctic-embed2',
            self::BGE2 => 'bge-m3',
        };
    }
    public function fieldName(): string
    {
        return match ($this) {
            self::ENBEDDRUS => 'embeddingEnbeddrus',
            self::ARCTIC2 => 'embeddingArctic2',
            self::BGE2 => 'embeddingBgeM3',
        };
    }
    public function columnName(): string
    {
        return match ($this) {
            self::ENBEDDRUS => 'embedding_enbeddrus',
            self::ARCTIC2 => 'embedding_arctic_2',
            self::BGE2 => 'embedding_bge_m3',
        };
    }
}
