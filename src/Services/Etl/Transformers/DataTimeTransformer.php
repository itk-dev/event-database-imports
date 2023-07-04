<?php


namespace App\Services\Etl\Transformers;

use Wizaplace\Etl\Row;
use Wizaplace\Etl\Transformers\Transformer;

class DataTimeTransformer extends Transformer
{
    /**
     * Transformer columns.
     *
     * @var string[]
     */
    protected array $columns = [];

    /**
     * Properties that can be set via the options method.
     *
     * @var string[]
     */
    protected array $availableOptions = [
        self::COLUMNS,
    ];

    /**
     * Sets default timezone.
     */
    public function __construct()
    {

    }

    public function transform(Row $row): void
    {
        $row->transform($this->columns, function ($column): \DateTimeImmutable {
            return new \DateTimeImmutable($column);
        });
    }
}
