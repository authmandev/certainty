<?php

namespace App\Services\Calculation;

/**
 * The values the legacy Cassandra form fields would hold (BRD §25.3), which is
 * the exact input the legacy engine reads. Null means an empty field.
 *
 * @phpstan-type Loan array{bank: string|null, amount: int|float|string|null, interest: int|float|string|null, years: int|float|string|null}
 * @phpstan-type Asset array{name: string|null, value: int|float|string|null, life_span: int|float|string|null, depreciation: int|float|string|null, includes_vat: bool}
 * @phpstan-type Employee array{title: string|null, total: int|float|string|null, salary: int|float|string|null}
 * @phpstan-type FixedCost array{name: string|null, amount: int|float|string|null, includes_vat: bool}
 * @phpstan-type Product array{name: string|null, vc: int|float|string|null, quantity: int|float|string|null, price: int|float|string|null, capacity: int|float|string|null, yearly_increase: int|float|string|null}
 */
final readonly class LegacyForm
{
    /**
     * @param  list<Loan>  $loans
     * @param  list<Asset>  $assets
     * @param  list<Employee>  $employees
     * @param  list<FixedCost>  $fixedCosts
     * @param  list<Product>  $products
     */
    public function __construct(
        public int|float|string|null $vat,
        public int|float|string|null $studyYears,
        public int|float|string|null $marketValue,
        public array $loans = [],
        public array $assets = [],
        public array $employees = [],
        public array $fixedCosts = [],
        public array $products = [],
    ) {}

}
