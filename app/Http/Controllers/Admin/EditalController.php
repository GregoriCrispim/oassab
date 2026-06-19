<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEditalRequest;
use App\Models\Edital;
use App\Models\EditalAttachment;
use App\Services\EditalFileStorage;
use App\Support\UploadedFileHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EditalController extends Controller
{
    public function __construct(
        private readonly EditalFileStorage $files,
    ) {}

    public function index(Request $request): View
    {
        $query = Edital::query()->withCount('attachments')->ordered();

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $editais = $query->paginate(15)->withQueryString();

        return view('admin.editais.index', [
            'editais' => $editais,
            'currentSearch' => $request->string('search')->toString(),
        ]);
    }

    public function create(): View
    {
        $edital = new Edital([
            'date' => now()->toDateString(),
            'is_published' => true,
            'sort_order' => 0,
        ]);

        return view('admin.editais.form', compact('edital'));
    }

    public function store(StoreEditalRequest $request): RedirectResponse
    {
        $edital = new Edital($request->payload());

        if (UploadedFileHelper::valid($request, 'file')) {
            $this->files->storeMainPdf($request->file('file'), $edital);
        }

        $edital->save();
        $this->syncAttachments($request, $edital);

        return redirect()
            ->route('admin.editais.index')
            ->with('status', 'Edital criado com sucesso.');
    }

    public function edit(Edital $edital): View
    {
        $edital->load('attachments');

        return view('admin.editais.form', compact('edital'));
    }

    public function update(StoreEditalRequest $request, Edital $edital): RedirectResponse
    {
        $oldSlug = $edital->slug;
        $edital->fill($request->payload());

        if ($request->boolean('remove_file') && $edital->hasMainFile()) {
            $this->files->deleteMain($edital);
            $edital->file_path = null;
            $edital->drive_file_id = null;
            $edital->original_filename = null;
        }

        if (UploadedFileHelper::valid($request, 'file')) {
            $this->files->deleteMain($edital);
            $this->files->storeMainPdf($request->file('file'), $edital);
        } elseif ($oldSlug !== $edital->slug && ! $this->files->usesGoogleDrive()) {
            $this->files->renameLocalFolder($edital, $oldSlug);
        }

        $edital->save();
        $this->removeMarkedAttachments($request, $edital);
        $this->syncAttachments($request, $edital);

        return redirect()
            ->route('admin.editais.index')
            ->with('status', 'Edital atualizado com sucesso.');
    }

    public function destroy(Edital $edital): RedirectResponse
    {
        $this->purgeAllFiles($edital);
        $edital->delete();

        return redirect()
            ->route('admin.editais.index')
            ->with('status', 'Edital excluído.');
    }

    private function purgeAllFiles(Edital $edital): void
    {
        $edital->load('attachments');

        if ($edital->hasMainFile()) {
            $this->files->deleteMain($edital);
        }

        foreach ($edital->attachments as $attachment) {
            $this->files->deleteAttachment($attachment);
        }

        if (! $this->files->usesGoogleDrive()) {
            $this->files->purgeAllLocal($edital);
        }
    }

    private function removeMarkedAttachments(StoreEditalRequest $request, Edital $edital): void
    {
        $ids = array_filter((array) $request->input('remove_attachments', []));

        if ($ids === []) {
            return;
        }

        $edital->attachments()
            ->whereIn('id', $ids)
            ->get()
            ->each(function (EditalAttachment $attachment) {
                $this->files->deleteAttachment($attachment);
                $attachment->delete();
            });
    }

    private function syncAttachments(StoreEditalRequest $request, Edital $edital): void
    {
        $titles = (array) $request->input('attachment_titles', []);
        $files = (array) $request->file('attachment_files', []);
        $maxSort = (int) $edital->attachments()->max('sort_order');

        foreach ($files as $index => $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            $title = trim((string) ($titles[$index] ?? '')) ?: $file->getClientOriginalName();
            $extension = strtolower($file->getClientOriginalExtension() ?: 'pdf');
            $storedName = Str::uuid()->toString().'.'.$extension;
            $stored = $this->files->storeAttachment($file, $edital, $storedName);

            $edital->attachments()->create([
                'title' => $title,
                'file_path' => $stored['file_path'],
                'drive_file_id' => $stored['drive_file_id'],
                'original_filename' => $file->getClientOriginalName(),
                'sort_order' => ++$maxSort,
            ]);
        }
    }
}
