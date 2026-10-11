<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportFilterRequest;
use App\Models\Category;
use App\Models\Department;
use App\Services\ReportService;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function index(ReportFilterRequest $request): View
    {
        $user = $request->user();
        $reportType = $request->query('report_type', 'cashflow');
        $filters = $request->validated();

        $departments = Department::where('status', 'active')->get();
        $categories = Category::active()->get();

        $reportData = match ($reportType) {
            'reimbursement' => $this->reportService->getReimbursementReport($filters, $user, true),
            'budget' => $this->reportService->getBudgetReport($filters, $user, true),
            default => $this->reportService->getCashFlowReport($filters, $user, true),
        };

        return view('reports.index', compact(
            'reportType',
            'reportData',
            'departments',
            'categories',
            'filters'
        ));
    }

    public function export(ReportFilterRequest $request): StreamedResponse
    {
        $user = $request->user();
        $reportType = $request->query('report_type', 'cashflow');
        $filters = $request->validated();

        return $this->reportService->exportToCsv($reportType, $filters, $user);
    }

    public function print(ReportFilterRequest $request): View
    {
        $user = $request->user();
        $reportType = $request->query('report_type', 'cashflow');
        $filters = $request->validated();

        $reportData = match ($reportType) {
            'reimbursement' => $this->reportService->getReimbursementReport($filters, $user, false),
            'budget' => $this->reportService->getBudgetReport($filters, $user, false),
            default => $this->reportService->getCashFlowReport($filters, $user, false),
        };

        return view('reports.print', compact(
            'reportType',
            'reportData',
            'filters',
            'user'
        ));
    }
}
