<?php

namespace Database\Seeders;

use App\Enums\ReferenceGroup;
use App\Models\ReferenceOption;
use Illuminate\Database\Seeder;

/**
 * Seeds the controlled dropdown values (BRD FR-008, A-08).
 *
 * PROVISIONAL: these lists were prepared by the development team and still
 * need client approval before production. Sectors follow the top-level
 * sections of ISIC Rev. 4, which the Saudi national economic activities
 * classification is based on. Codes are stable; labels may be edited safely.
 * Safe to re-run: options are matched by group and code.
 */
class ReferenceOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->options() as $group => $options) {
            foreach ($options as $index => [$code, $label]) {
                ReferenceOption::query()->updateOrCreate(
                    ['group' => $group, 'code' => $code],
                    ['label_ar' => $label, 'sort_order' => $index + 1, 'is_active' => true],
                );
            }
        }
    }

    /**
     * @return array<string, list<array{0: string, 1: string}>>
     */
    private function options(): array
    {
        return [
            ReferenceGroup::Sector->value => [
                ['isic4-a', 'الزراعة والحراجة وصيد الأسماك'],
                ['isic4-b', 'التعدين واستغلال المحاجر'],
                ['isic4-c', 'الصناعات التحويلية'],
                ['isic4-d', 'إمدادات الكهرباء والغاز والبخار وتكييف الهواء'],
                ['isic4-e', 'إمدادات المياه وأنشطة الصرف الصحي وإدارة النفايات ومعالجتها'],
                ['isic4-f', 'التشييد'],
                ['isic4-g', 'تجارة الجملة والتجزئة وإصلاح المركبات'],
                ['isic4-h', 'النقل والتخزين'],
                ['isic4-i', 'أنشطة خدمات الإقامة والطعام'],
                ['isic4-j', 'المعلومات والاتصالات'],
                ['isic4-k', 'الأنشطة المالية وأنشطة التأمين'],
                ['isic4-l', 'الأنشطة العقارية'],
                ['isic4-m', 'الأنشطة المهنية والعلمية والتقنية'],
                ['isic4-n', 'أنشطة الخدمات الإدارية وخدمات الدعم'],
                ['isic4-p', 'التعليم'],
                ['isic4-q', 'أنشطة صحة الإنسان والعمل الاجتماعي'],
                ['isic4-r', 'الفنون والترفيه والتسلية'],
                ['isic4-s', 'أنشطة الخدمات الأخرى'],
            ],
            ReferenceGroup::ProjectType->value => [
                ['retail-store', 'متجر تجزئة'],
                ['restaurant-cafe', 'مطعم أو مقهى'],
                ['services', 'مشروع خدمي'],
                ['manufacturing', 'مشروع صناعي أو إنتاجي'],
                ['online', 'مشروع إلكتروني أو تقني'],
                ['home-based', 'مشروع منزلي'],
                ['franchise', 'امتياز تجاري (فرنشايز)'],
                [ReferenceOption::OTHER, 'أخرى'],
            ],
            ReferenceGroup::AssetCategory->value => [
                ['land-buildings', 'أراضٍ ومبانٍ'],
                ['fit-out', 'تجهيزات وديكورات'],
                ['machinery', 'آلات ومعدات'],
                ['furniture', 'أثاث'],
                ['it-equipment', 'أجهزة حاسب وتقنية'],
                ['vehicles', 'سيارات ومركبات'],
                ['licenses', 'رسوم تأسيس وتراخيص'],
                ['initial-inventory', 'مخزون أولي'],
                [ReferenceOption::OTHER, 'أخرى'],
            ],
            ReferenceGroup::FixedCostCategory->value => [
                ['rent', 'إيجار'],
                ['utilities', 'كهرباء ومياه'],
                ['telecom', 'اتصالات وإنترنت'],
                ['maintenance', 'صيانة'],
                ['marketing', 'تسويق وإعلان'],
                ['insurance', 'تأمين'],
                ['government-fees', 'رسوم حكومية واشتراكات'],
                ['cleaning', 'نظافة'],
                [ReferenceOption::OTHER, 'أخرى'],
            ],
            ReferenceGroup::JobCategory->value => [
                ['manager', 'مدير'],
                ['accountant', 'محاسب'],
                ['sales', 'موظف مبيعات'],
                ['cashier', 'كاشير'],
                ['production-worker', 'عامل إنتاج'],
                ['chef', 'طاهٍ'],
                ['barista', 'باريستا'],
                ['driver', 'سائق'],
                ['technician', 'فني'],
                ['customer-service', 'خدمة عملاء'],
                ['cleaner', 'عامل نظافة'],
                [ReferenceOption::OTHER, 'أخرى'],
            ],
        ];
    }
}
