<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEmployeeDocumentRequest;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeDocumentController extends Controller
{
    public function store(StoreEmployeeDocumentRequest $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validated();
        $file = $request->file('document');

        $path = $file->store('employee-documents/'.$employee->id, 'local');

        $employee->documents()->create([
            'type' => $validated['type'],
            'title' => $validated['title'],
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'document_number' => $validated['document_number'] ?? null,
            'issued_on' => $validated['issued_on'] ?? null,
            'expires_on' => $validated['expires_on'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('admin.employees.show', $employee)
            ->with('success', 'Document uploaded.');
    }

    public function download(Employee $employee, EmployeeDocument $document): StreamedResponse
    {
        abort_unless($document->employee_id === $employee->id, 404);
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        return Storage::disk('local')->download(
            $document->file_path,
            $document->original_name ?: $document->title,
        );
    }

    public function destroy(Employee $employee, EmployeeDocument $document): RedirectResponse
    {
        abort_unless($document->employee_id === $employee->id, 404);

        $document->deleteFile();
        $document->delete();

        return redirect()
            ->route('admin.employees.show', $employee)
            ->with('success', 'Document removed.');
    }
}
