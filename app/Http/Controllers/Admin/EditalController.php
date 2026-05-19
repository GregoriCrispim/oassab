<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEditalRequest;
use App\Models\Edital;
use App\Models\EditalAttachment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EditalController extends Controller
{
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

        if ($request->hasFile('file')) {
            $this->storeMainPdf($request->file('file'), $edital);
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

        if ($request->boolean('remove_file') && $edital->file_path) {
            $this->deleteMainPdf($edital);
            $edital->file_path = null;
            $edital->original_filename = null;
        }

        if ($request->hasFile('file')) {
            $this->deleteMainPdf($edital);
            $this->storeMainPdf($request->file('file'), $edital);
        } elseif ($oldSlug !== $edital->slug) {
            $this->renameStorageFolder($edital, $oldSlug);
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

    private function storeMainPdf(UploadedFile $file, Edital $edital): void
    {
        $relativePath = $file->storeAs(
            'editais/'.$edital->slug,
            $edital->slug.'.pdf',
            'public'
        );

        $edital->file_path = '/storage/'.$relativePath;
        $edital->original_filename = $file->getClientOriginalName();
    }

    private function deleteMainPdf(Edital $edital): void
    {
        if (! $edital->file_path || ! Str::startsWith($edital->file_path, '/storage/')) {
            return;
        }

        $relative = Str::after($edital->file_path, '/storage/');
        Storage::disk('public')->delete($relative);
    }

    private function purgeAllFiles(Edital $edital): void
    {
        Storage::disk('public')->deleteDirectory('editais/'.$edital->slug);
    }

    private function renameStorageFolder(Edital $edital, string $oldSlug): void
    {
        $oldFolder = 'editais/'.$oldSlug;
        $newFolder = 'editais/'.$edital->slug;

        if (! Storage::disk('public')->exists($oldFolder)) {
            return;
        }

        Storage::disk('public')->move($oldFolder, $newFolder);

        if ($edital->file_path) {
            $edital->file_path = '/storage/'.$newFolder.'/'.$edital->slug.'.pdf';
        }

        foreach ($edital->attachments as $attachment) {
            if (Str::startsWith($attachment->file_path, '/storage/')) {
                $relative = Str::after($attachment->file_path, '/storage/');
                $newRelative = preg_replace('#^editais/'.preg_quote($oldSlug, '#').'#', 'editais/'.$edital->slug, $relative);
                $attachment->update(['file_path' => '/storage/'.$newRelative]);
            }
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
                $this->deleteAttachmentFile($attachment);
                $attachment->delete();
            });
    }

    private function syncAttachments(StoreEditalRequest $request, Edital $edital): void
    {
        $titles = (array) $request->input('attachment_titles', []);
        $files = (array) $request->file('attachment_files', []);
        $maxSort = (int) $edital->attachments()->max('sort_order');

        foreach ($files as $index => $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $title = trim((string) ($titles[$index] ?? '')) ?: $file->getClientOriginalName();
            $storedName = Str::uuid()->toString().'.pdf';
            $relativePath = $file->storeAs('editais/'.$edital->slug.'/anexos', $storedName, 'public');

            $edital->attachments()->create([
                'title' => $title,
                'file_path' => '/storage/'.$relativePath,
                'original_filename' => $file->getClientOriginalName(),
                'sort_order' => ++$maxSort,
            ]);
        }
    }

    private function deleteAttachmentFile(EditalAttachment $attachment): void
    {
        if (! $attachment->file_path || ! Str::startsWith($attachment->file_path, '/storage/')) {
            return;
        }

        $relative = Str::after($attachment->file_path, '/storage/');
        Storage::disk('public')->delete($relative);
    }
}
