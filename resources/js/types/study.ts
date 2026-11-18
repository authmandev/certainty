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
    days_remaining: number | null;
    expires_at: string;
    created_at: string;
    updated_at: string;
};
