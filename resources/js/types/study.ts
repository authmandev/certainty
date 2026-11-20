export type StudyStatus = 'draft' | 'expired' | 'finalized';

export type PaymentStatus =
    | 'pending'
    | 'under_review'
    | 'approved'
    | 'rejected';

export type LabelledStatus<T extends string> = {
    value: T;
    label: string;
};

export type StudyPayment = {
    reference_number: string;
    amount: string;
    currency: string;
    status: LabelledStatus<PaymentStatus>;
};

export type Study = {
    id: number;
    reference: string;
    title: string;
    project_name: string;
    status: LabelledStatus<StudyStatus>;
    payment?: StudyPayment | null;
    completion_percentage: number;
    is_editable: boolean;
    days_remaining: number | null;
    expires_at: string;
    created_at: string;
    updated_at: string;
};

export type OptionItem<T extends string | number = string> = {
    value: T;
    label: string;
};

export type Nullable<T> = { [K in keyof T]: T[K] | null };

export type OwnerRow = Nullable<{
    key: string;
    name: string;
    share: number;
    experience: string;
}>;
export type CompetitorRow = Nullable<{
    key: string;
    name: string;
    share: number;
    characteristics: string;
}>;
export type AssetRow = Nullable<{
    key: string;
    category_code: string;
    name: string;
    value: number;
    life_span: number;
    depreciation_rate: number;
}> & { includes_vat: boolean };
export type LoanRow = Nullable<{
    key: string;
    lender: string;
    amount: number;
    interest_rate: number;
    years: number;
}>;
export type FixedCostRow = Nullable<{
    key: string;
    category_code: string;
    name: string;
    monthly_amount: number;
}> & { includes_vat: boolean };
export type EmployeeRow = Nullable<{
    key: string;
    job_code: string;
    title: string;
    count: number;
    monthly_salary: number;
}>;
export type ProductRow = Nullable<{
    key: string;
    name: string;
    unit_cost: number;
    unit_price: number;
    monthly_quantity: number;
    monthly_capacity: number;
    annual_growth: number;
}>;

export type StudyInput = {
    settings: Nullable<{
        currency: string;
        decimals: number;
        vat_percent: number;
        study_years: number;
    }>;
    project: Nullable<{
        start_date: string;
        description: string;
        organizational_feasibility: string;
        sector_code: string;
        project_type_code: string;
        project_type_other: string;
    }>;
    owners: OwnerRow[];
    market: Nullable<{
        name: string;
        size: number;
        description: string;
        products: string;
        target_customers: string;
        suppliers: string;
        marketing_plan: string;
        competition_analysis: string;
    }>;
    competitors: CompetitorRow[];
    assets: AssetRow[];
    loans: LoanRow[];
    fixed_costs: FixedCostRow[];
    employees: EmployeeRow[];
    products: ProductRow[];
    swot: Nullable<{
        strengths: string;
        weaknesses: string;
        opportunities: string;
        threats: string;
    }>;
};

export type StudyFormData = StudyInput & {
    title: string;
    project_name: string;
};

export type StudyFormOptions = {
    currencies: OptionItem[];
    decimals: number[];
    vatPercents: number[];
    studyYears: number[];
    sectors: OptionItem[];
    projectTypes: OptionItem[];
    assetCategories: OptionItem[];
    fixedCostCategories: OptionItem[];
    jobCategories: OptionItem[];
};

export type SectionKey = 'basic' | 'market' | 'costs' | 'sales' | 'swot';

export type SectionProgress = {
    key: SectionKey;
    title: string;
    completed: number;
    total: number;
    is_complete: boolean;
    missing: string[];
};

export type StudyProgress = {
    percentage: number;
    sections: SectionProgress[];
};

export type PreviewWarning = {
    code: string;
    section: SectionKey | 'results';
    message: string;
};

export type StudyPreview = {
    calculation_version: string;
    currency: string | null;
    decimals: number;
    study_years: number;
    summary: {
        total_capital: number;
        total_borrowed: number;
        total_liabilities_payable: number;
        self_financing: number;
        unused_borrowed_funds: number;
        monthly_salaries: number;
        monthly_fixed_costs: number;
        monthly_revenue_without_vat: number;
        monthly_variable_costs_without_vat: number;
    };
    indicators: {
        total_sales: number;
        average_sales: number;
        average_profit: number;
        capital_recovery: {
            status: 'within' | 'more_than' | 'not_available';
            years: number | null;
            label: string;
        };
        market_share: number | null;
    };
    setup_costs: {
        name: string;
        value: number;
        is_study_fee: boolean;
        is_unused_borrowed_funds: boolean;
    }[];
    yearly: {
        year: number;
        revenue: number;
        expenses: number;
        profit: number;
        profit_ratio: number | null;
    }[];
    warnings: PreviewWarning[];
};

export type ReceiptStatus = 'submitted' | 'approved' | 'rejected' | 'replaced';

export type PaymentReceipt = {
    id: number;
    file_name: string;
    mime_type: string;
    file_size: number;
    url: string;
    status: LabelledStatus<ReceiptStatus>;
    submitted_at: string;
    reviewed_at: string | null;
    reviewer?: { name: string } | null;
    rejection_reason: string | null;
};

export type StudyPaymentDetail = StudyPayment & {
    id: number;
    accepts_receipts: boolean;
    approved_at: string | null;
    created_at: string;
    customer?: { name: string; email: string };
    study?: {
        id: number;
        reference: string;
        title: string;
        status: LabelledStatus<StudyStatus>;
        completion_percentage: number;
        report_url: string | null;
    };
    latest_receipt?: PaymentReceipt | null;
    receipts?: PaymentReceipt[];
};

export type BankDetails = {
    name: string | null;
    account_name: string | null;
    iban: string | null;
};

export type ReceiptRules = {
    extensions: string[];
    max_kilobytes: number;
};

export type Paginated<T> = {
    data: T[];
    links: {
        first: string | null;
        last: string | null;
        prev: string | null;
        next: string | null;
    };
    meta: {
        current_page: number;
        last_page: number;
        from: number | null;
        to: number | null;
        total: number;
        per_page: number;
    };
};

export type FinalizationCheck = {
    key: 'not_expired' | 'complete' | 'calculable' | 'payment_approved';
    label: string;
    passed: boolean;
    detail: string | null;
};

export type Finalization = {
    checks: FinalizationCheck[];
    ready: boolean;
};

export type StudyReportVersion = {
    id: number;
    version: number;
    is_generated: boolean;
    finalized_at: string;
    generated_at: string | null;
    view_url: string;
    download_url: string;
};
