<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [

            [
                'name' => 'Dashboard',
                'slug' => 'dashboard',
                'route_name' => 'dashboard',
                'active_routes' => 'dashboard*',
                'icon' => 'bi bi-speedometer2',
            ],

            [
                'name' => 'Role Management',
                'slug' => 'roles',
                'route_name' => 'roles.index',
                'active_routes' => 'roles.*',
                'icon' => 'bi bi-shield-lock',
            ],

            [
                'name' => 'User / Staff Management',
                'slug' => 'users',
                'route_name' => 'users.index',
                'active_routes' => 'users.*',
                'icon' => 'bi bi-people',
            ],
            [
                'name' => 'Plot Availability',
                'slug' => 'plot-availability',
                'icon' => 'bi bi-grid-1x2-fill',
                'route_name' => 'plot-availability.index',
                'active_routes' => 'plot-availability*',
            ],

            [
                'name' => 'Master',
                'slug' => 'master',
                'icon' => 'bi bi-grid',
            ],

            [
                'name' => 'Company Profile',
                'slug' => 'company-profile',
                'parent_slug' => 'master',
                'route_name' => 'company.index',
                'active_routes' => 'company.*',
                'icon' => 'bi bi-building',
            ],

            [
                'name' => 'Project',
                'slug' => 'project',
                'parent_slug' => 'master',
                'route_name' => 'projects.index',
                'active_routes' => 'projects.*',
                'icon' => 'bi bi-folder',
            ],

            [
                'name' => 'Block',
                'slug' => 'block',
                'parent_slug' => 'master',
                'route_name' => 'blocks.index',
                'active_routes' => 'blocks.*',
                'icon' => 'bi bi-grid-3x3-gap',
            ],

            [
                'name' => 'Plot Type',
                'slug' => 'plot-types',
                'parent_slug' => 'master',
                'route_name' => 'plot-types.index',
                'active_routes' => 'plot-types.*',
                'icon' => 'bi bi-diagram-3',
            ],

            [
                'name' => 'Plot Detail',
                'slug' => 'plot-details',
                'parent_slug' => 'master',
                'route_name' => 'plot-details.index',
                'active_routes' => 'plot-details.*',
                'icon' => 'bi bi-card-list',
            ],

            [
                'name' => 'PLC / Development Rate',
                'slug' => 'plot-rates',
                'parent_slug' => 'master',
                'route_name' => 'plot-rates.index',
                'active_routes' => 'plot-rates.*,'.
                    'plc-rates.*,'.
                    'developments.*',
                'icon' => 'bi bi-currency-rupee',
            ],

            [
                'name' => 'Project Manipulation',
                'slug' => 'project.manipulation',
                'parent_slug' => 'master',
                'route_name' => 'project.manipulation.index',
                'active_routes' => 'project.manipulation.*',
                'icon' => 'bi bi-sliders',
            ],

            [
                'name' => 'Rank / Designation',
                'slug' => 'designations',
                'parent_slug' => 'master',
                'route_name' => 'designations.index',
                'active_routes' => 'designations.*',
                'icon' => 'bi bi-award',
            ],

            [
                'name' => 'Associate',
                'slug' => 'associate',
                'icon' => 'bi bi-person-lines-fill',
            ],

            [
                'name' => 'Associate Tree',
                'slug' => 'associate-tree',
                'parent_slug' => 'associate',
                'route_name' => 'associate-tree',
                'active_routes' => 'associate-tree*',
                'icon' => 'bi bi-diagram-3',
            ],

            [
                'name' => 'Add Associate',
                'slug' => 'associate',
                'parent_slug' => 'associate',
                'route_name' => 'associate.create',
                'active_routes' => 'associate.create*',
                'icon' => 'bi bi-person-plus',
            ],

            [
                'name' => 'Associate Details',
                'slug' => 'associate-details',
                'parent_slug' => 'associate',
                'route_name' => 'associate.index',
                'active_routes' => 'associate.index,associate.edit,associate.show',
                'icon' => 'bi bi-person-vcard',
            ],

            [
                'name' => 'Direct Associate',
                'slug' => 'direct-associate',
                'parent_slug' => 'associate',
                'route_name' => 'direct-associate',
                'active_routes' => 'direct-associate*',
                'icon' => 'bi bi-people-fill',
            ],

            [
                'name' => 'Downline Associate',
                'slug' => 'associate-downline',
                'parent_slug' => 'associate',
                'route_name' => 'associate-downline',
                'active_routes' => 'associate-downline*',
                'icon' => 'bi bi-diagram-2-fill',
            ],

            [
                'name' => 'Promotion Report',
                'slug' => 'promotion.report',
                'parent_slug' => 'associate',
                'route_name' => 'promotion.report',
                'active_routes' => 'promotion.report*',
                'icon' => 'bi bi-graph-up-arrow',
            ],

            [
                'name' => 'Plot Booking / Sales',
                'slug' => 'plot-booking',
                'icon' => 'bi bi-house-check',
            ],

            [
                'name' => 'Customer Booking',
                'slug' => 'customer-booking',
                'parent_slug' => 'plot-booking',
                'route_name' => 'customer-booking.index',
                'active_routes' => 'customer-booking*',
                'icon' => 'bi bi-people-fill',
            ],

            [
                'name' => 'Customer List',
                'slug' => 'customer-list',
                'parent_slug' => 'plot-booking',
                'route_name' => 'customer-list.index',
                'active_routes' => 'customer-list.index',
                'icon' => 'bi bi-list-ul',
            ],

            [
                'name' => 'Edit Plot Booking',
                'slug' => 'edit-plot-booking',
                'parent_slug' => 'plot-booking',
                'route_name' => 'edit-plot-booking.index',
                'active_routes' => 'edit-plot-booking.index',
                'icon' => 'bi bi-pencil-square',
            ],

            [
                'name' => 'Plot Registry',
                'slug' => 'plot-registry',
                'parent_slug' => 'plot-booking',
                'route_name' => 'plot-registry.index',
                'active_routes' => 'plot-registry*',
                'icon' => 'bi bi-journal-check',
            ],

            [
                'name' => 'Cancel Booking',
                'slug' => 'cancel-booking',
                'parent_slug' => 'plot-booking',
                'route_name' => 'cancel-booking.index',
                'active_routes' => 'cancel-booking*',
                'icon' => 'bi bi-x-circle',
            ],

            [
                'name' => 'Plot Transfer',
                'slug' => 'plot-transfer',
                'parent_slug' => 'plot-booking',
                'route_name' => 'plot-transfer.index',
                'active_routes' => 'plot-transfer*',
                'icon' => 'bi bi-arrow-left-right',
            ],

            [
                'name' => 'Allotement & Agreement Letter',
                'slug' => 'booking-letter',
                'parent_slug' => 'plot-booking',
                'route_name' => 'booking-letter.index',
                'active_routes' => 'booking-letter*',
                'icon' => 'bi bi-file-earmark-text',
            ],

            // Parent Module
            [
                'name' => 'Payment',
                'slug' => 'payment',
                'icon' => 'bi bi-wallet2',
            ],

            // Child Modules

            [
                'name' => 'Receipt Reprint',
                'slug' => 'receipt-reprint',
                'parent_slug' => 'payment',
                'route_name' => 'receipt-reprint.index',
                'active_routes' => 'receipt-reprint*',
                'icon' => 'bi bi-receipt',
            ],

            [
                'name' => 'One Time Payment',
                'slug' => 'one-time-payment',
                'parent_slug' => 'payment',
                'route_name' => 'one-time-payment.index',
                'active_routes' => 'one-time-payment*',
                'icon' => 'bi bi-cash',
            ],

            [
                'name' => 'EMI Payment',
                'slug' => 'emi-payment',
                'parent_slug' => 'payment',
                'route_name' => 'emi-payment.index',
                'active_routes' => 'emi-payment*',
                'icon' => 'bi bi-calendar-check',
            ],

            [
                'name' => 'Multiple Cheque Clearance',
                'slug' => 'multiple-cheque-clearance',
                'parent_slug' => 'payment',
                'route_name' => 'multiple-cheque-clearance.index',
                'active_routes' => 'multiple-cheque-clearance*',
                'icon' => 'bi bi-bank',
            ],

            [
                'name' => 'Edit Payment Detail',
                'slug' => 'payment-edit',
                'parent_slug' => 'payment',
                'route_name' => 'edit-payment-details.index',
                'active_routes' => 'edit-payment-details*',
                'icon' => 'bi bi-pencil-square',
            ],

            [
                'name' => 'Update EMI Date',
                'slug' => 'update-emi-date',
                'parent_slug' => 'payment',
                'route_name' => 'update-emi-date.index',
                'active_routes' => 'update-emi-date*',
                'icon' => 'bi bi-calendar-event',
            ],

            [
                'name' => 'Associate Advance',
                'slug' => 'associate-advance',
                'parent_slug' => 'payment',
                'route_name' => 'associate-advances.index',
                'active_routes' => 'associate-advances.*',
                'icon' => 'bi bi-person-badge',
            ],

            [
                'name' => 'Generate EMI',
                'slug' => 'generate-emi',
                'parent_slug' => 'payment',
                'route_name' => 'generate-emi.index',
                'active_routes' => 'generate-emi*',
                'icon' => 'bi bi-gear',
            ],

            // Parent Module
            [
                'name' => 'Report',
                'slug' => 'report',
                'icon' => 'bi bi-clipboard-data',
            ],

            // Child Modules
            [
                'name' => 'Agent Details',
                'slug' => 'agent-detail-report',
                'parent_slug' => 'report',
                'route_name' => 'agent-detail-report.index',
                'active_routes' => 'agent-detail-report*',
                'icon' => 'bi bi-person-badge',
            ],

            [
                'name' => 'Customer Details',
                'slug' => 'customer-details-report',
                'parent_slug' => 'report',
                'route_name' => 'customer-details-report.index',
                'active_routes' => 'customer-details-report*',
                'icon' => 'bi bi-people',
            ],
            [
                'name' => 'EMI Due Status',
                'slug' => 'emi-due-status-report',
                'parent_slug' => 'report',
                'route_name' => 'emi-due-status-report.index',
                'active_routes' => 'emi-due-status-report*',
                'icon' => 'bi bi-calendar-x',
            ],

            [
                'name' => 'EMI Payment Dues',
                'slug' => 'emi-payment-dues-report',
                'parent_slug' => 'report',
                'route_name' => 'emi-due-date-report.index',
                'active_routes' => 'emi-due-date-report*',
                'icon' => 'bi bi-cash-stack',
            ],

            [
                'name' => 'One Time Payment Dues',
                'slug' => 'one-time-payment-dues-report',
                'parent_slug' => 'report',
                'route_name' => 'one-time-payment-dues-report.index',
                'active_routes' => 'one-time-payment-dues-report*',
                'icon' => 'bi bi-wallet2',
            ],

            [
                'name' => 'Plot Booking Details',
                'slug' => 'plot-booking-details-report',
                'parent_slug' => 'report',
                'route_name' => 'plot-booking-details-report.index',
                'active_routes' => 'plot-booking-details-report*',
                'icon' => 'bi bi-house-check',
            ],

            [
                'name' => 'EMI Payment Details',
                'slug' => 'emi-payment-details-report',
                'parent_slug' => 'report',
                'route_name' => 'emi-payment-details-report.index',
                'active_routes' => 'emi-payment-details-report*',
                'icon' => 'bi bi-calendar-check',
            ],

            [
                'name' => 'Full Payment Details',
                'slug' => 'full-payment-details-report',
                'parent_slug' => 'report',
                'route_name' => 'full-payment-details-report.index',
                'active_routes' => 'full-payment-details-report*',
                'icon' => 'bi bi-credit-card',
            ],

            [
                'name' => 'Registerd Plot Details',
                'slug' => 'registered-plot-details-report',
                'parent_slug' => 'report',
                'route_name' => 'registered-plot-details-report.index',
                'active_routes' => 'registered-plot-details-report*',
                'icon' => 'bi bi-file-earmark-check',
            ],

            [
                'name' => 'Without Registerd Plot Report',
                'slug' => 'without-registered-plot-report',
                'parent_slug' => 'report',
                'route_name' => 'without-registered-plot-report.index',
                'active_routes' => 'without-registered-plot-report*',
                'icon' => 'bi bi-file-earmark-x',
            ],

            [
                'name' => 'Associate Direct Report',
                'slug' => 'associate-direct-report',
                'parent_slug' => 'report',
                'route_name' => 'associate-direct-report.index',
                'active_routes' => 'associate-direct-report*',
                'icon' => 'bi bi-diagram-2',
            ],

            [
                'name' => 'Associate Chain Report',
                'slug' => 'associate-chain-report',
                'parent_slug' => 'report',
                'route_name' => 'associate-chain-report.index',
                'active_routes' => 'associate-chain-report*',
                'icon' => 'bi bi-diagram-3',
            ],

            [
                'name' => 'Associate Business Report',
                'slug' => 'associate-business-report',
                'parent_slug' => 'report',
                'route_name' => 'associate-business-report.index',
                'active_routes' => 'associate-business-report*',
                'icon' => 'bi bi-graph-up',
            ],

            [
                'name' => 'Cancel Plot Booking Report',
                'slug' => 'cancel-plot-booking-report',
                'parent_slug' => 'report',
                'route_name' => 'cancel-plot-booking-report.index',
                'active_routes' => 'cancel-plot-booking-report*',
                'icon' => 'bi bi-x-circle',
            ],

            [
                'name' => 'Customer Ledger',
                'slug' => 'customer-ledger-report',
                'parent_slug' => 'report',
                'route_name' => 'customer-ledger-report.index',
                'active_routes' => 'customer-ledger-report*',
                'icon' => 'bi bi-journal-text',
            ],
            [
                'name' => 'Payment Collection & Dues Summary',
                'slug' => 'payment-collection-dues-summary-report',
                'parent_slug' => 'report',
                'route_name' => 'payment-collection-dues-summary-report.index',
                'active_routes' => 'payment-collection-dues-summary-report*',
                'icon' => 'bi bi-cash-stack',
            ],

            [
                'name' => 'Dues Installment Report',
                'slug' => 'dues-installment-report',
                'parent_slug' => 'report',
                'route_name' => 'dues-installment-report.index',
                'active_routes' => 'dues-installment-report*',
                'icon' => 'bi bi-calendar2-check',
            ],

            [
                'name' => 'Cheque Details',
                'slug' => 'cheque-details-report',
                'parent_slug' => 'report',
                'route_name' => 'cheque-details-report.index',
                'active_routes' => 'cheque-details-report*',
                'icon' => 'bi bi-bank',
            ],

            [
                'name' => 'EMI Dues Summary',
                'slug' => 'emi-dues-summary-report',
                'parent_slug' => 'report',
                'route_name' => 'emi-dues-summary-report.index',
                'active_routes' => 'emi-dues-summary-report*',
                'icon' => 'bi bi-wallet2',
            ],

            [
                'name' => 'Daily Collection Report',
                'slug' => 'daily-collection-report',
                'parent_slug' => 'report',
                'route_name' => 'daily-collection-report.index',
                'active_routes' => 'daily-collection-report*',
                'icon' => 'bi bi-calendar-day',
            ],

            [
                'name' => 'Daily Dues Report',
                'slug' => 'daily-dues-report',
                'parent_slug' => 'report',
                'route_name' => 'daily-dues-report.index',
                'active_routes' => 'daily-dues-report*',
                'icon' => 'bi bi-calendar-x',
            ],

            [
                'name' => 'Agent Summary Details',
                'slug' => 'agent-summary-details-report',
                'parent_slug' => 'report',
                'route_name' => 'agent-summary-details-report.index',
                'active_routes' => 'agent-summary-details-report*',
                'icon' => 'bi bi-person-lines-fill',
            ],

            [
                'name' => 'New Booking Payment Details',
                'slug' => 'new-booking-payment-details-report',
                'parent_slug' => 'report',
                'route_name' => 'new-booking-payment-details-report.index',
                'active_routes' => 'new-booking-payment-details-report*',
                'icon' => 'bi bi-house-add',
            ],

            [
                'name' => 'Associate Team New Booking Details',
                'slug' => 'associate-team-new-booking-details-report',
                'parent_slug' => 'report',
                'route_name' => 'associate-team-new-booking-details-report.index',
                'active_routes' => 'associate-team-new-booking-details-report*',
                'icon' => 'bi bi-people-fill',
            ],

            [
                'name' => 'Bounced Cheque Details',
                'slug' => 'bounced-cheque-details-report',
                'parent_slug' => 'report',
                'route_name' => 'bounced-cheque-details-report.index',
                'active_routes' => 'bounced-cheque-details-report*',
                'icon' => 'bi bi-x-circle',
            ],

            [
                'name' => 'Associate Advance Report',
                'slug' => 'associate-advance-report',
                'parent_slug' => 'report',
                'route_name' => 'associate-advance-report.index',
                'active_routes' => 'associate-advance-report*',
                'icon' => 'bi bi-currency-rupee',
            ],

            // [
            //     'name' => 'Cancel Plot Report',
            //     'slug' => 'cancel-plot-report',
            //     'parent_slug' => 'report',
            //     'route_name' => 'cancel-plot-report.index',
            //     'active_routes' => 'cancel-plot-report*',
            //     'icon' => 'bi bi-file-earmark-x',
            // ],

            // [
            //     'name' => 'Monthly EMI Report',
            //     'slug' => 'monthly-emi-report',
            //     'parent_slug' => 'report',
            //     'route_name' => 'monthly-emi-report.index',
            //     'active_routes' => 'monthly-emi-report*',
            //     'icon' => 'bi bi-calendar3',
            // ],

            [
                'name' => 'Lead Management',
                'slug' => 'lead-management',
                'icon' => 'bi bi-briefcase',
            ],

            [
                'name' => 'Source',
                'slug' => 'lead-source',
                'parent_slug' => 'lead-management',
                'route_name' => 'source.index',
                'active_routes' => 'source*',
                'icon' => 'bi bi-box-arrow-in-right',
            ],
            [
                'name' => 'Enquiry Type',
                'slug' => 'enquiry-type',
                'parent_slug' => 'lead-management',
                'route_name' => 'enquiry-type.index',
                'active_routes' => 'enquiry-type*',
                'icon' => 'bi bi-list-stars',
            ],

            [
                'name' => 'New Enquiry',
                'slug' => 'new-enquiry',
                'parent_slug' => 'lead-management',
                'route_name' => 'enquiry.index',
                'active_routes' => 'enquiry*',
                'icon' => 'bi bi-telephone-plus',
            ],
            [
                'name' => 'Support Management',
                'slug' => 'support-management',
                'icon' => 'bi bi-headset',
                'route_name' => 'support.index',
                'active_routes' => 'support*',
            ],

        ];
        foreach ($modules as $module) {
            $parentId = null;
            if (isset($module['parent_slug'])) {
                $parent = Module::where('slug', $module['parent_slug'])->first();
                $parentId = $parent?->id;
            }
            unset($module['parent_slug']);
            $module['parent_id'] = $parentId;
            Module::updateOrCreate(['slug' => $module['slug']], $module);
        }
    }
}
