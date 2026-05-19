<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTransparencyDocumentRequest;
use App\Models\TransparencyDocument;
use App\Services\PublicStoragePublisher;
use App\Support\UploadedFileHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TransparencyDocumentController extends Controller
{
    public function index(Request $request): View
    {
        $query = TransparencyDocument::query()->ordered();

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('processo', 'like', "%{$search}%");
            });
        }

        $documents = $query->paginate(15)->withQueryString();

        return view('admin.transparency-documents.index', [
            'documents' => $documents,
            'currentSearch' => $request->string('search')->toString(),
        ]);
    }

    public function create(): View
    {
        $document = new TransparencyDocument([
            'is_published' => true,
            'sort_order' => 0,
            'year' => (string) now()->year,
        ]);

        return view('admin.transparency-documents.form', compact('document'));
    }

    public function store(StoreTransparencyDocumentRequest $request): RedirectResponse
    {
        $document = new TransparencyDocument($request->payload());

        if (UploadedFileHelper::valid($request, 'file')) {
            $this->storePdf($request->file('file'), $document);
        }

        $document->save();
        PublicStoragePublisher::publish();

        return redirect()
            ->route('admin.transparency-documents.index')
            ->with('status', 'Documento criado com sucesso.');
    }

    public function edit(TransparencyDocument $transparency_document): View
    {
        return view('admin.transparency-documents.form', [
            'document' => $transparency_document,
        ]);
    }

    public function update(StoreTransparencyDocumentRequest $request, TransparencyDocument $transparency_document): RedirectResponse
    {
        $oldSlug = $transparency_document->slug;
        $transparency_document->fill($request->payload());

        if (UploadedFileHelper::valid($request, 'file')) {
            $this->purgePdf($transparency_document);
            $this->storePdf($request->file('file'), $transparency_document);
        } elseif ($oldSlug !== $transparency_document->slug && $transparency_document->file_path) {
            $this->renamePdfFolder($transparency_document, $oldSlug);
        }

        $transparency_document->save();
        PublicStoragePublisher::publish();

        return redirect()
            ->route('admin.transparency-documents.index')
            ->with('status', 'Documento atualizado com sucesso.');
    }

    public function destroy(TransparencyDocument $transparency_document): RedirectResponse
    {
        $this->purgePdf($transparency_document);
        $transparency_document->delete();
        PublicStoragePublisher::publish();

        return redirect()
            ->route('admin.transparency-documents.index')
            ->with('status', 'Documento excluído.');
    }

    private function storePdf(\Illuminate\Http\UploadedFile $file, TransparencyDocument $document): void
    {
        $filename = $document->slug.'.pdf';

        $relativePath = Storage::disk('public')->putFileAs(
            'transparency/'.$document->slug,
            $file,
            $filename
        );

        if (! $relativePath) {
            throw new \RuntimeException('Não foi possível salvar o PDF.');
        }

        $document->file_path = '/storage/'.$relativePath;
        $document->original_filename = $file->getClientOriginalName();
    }

    private function purgePdf(TransparencyDocument $document): void
    {
        if (! $document->file_path || ! Str::startsWith($document->file_path, '/storage/')) {
            return;
        }

        $relative = Str::after($document->file_path, '/storage/');
        $folder = dirname($relative);

        if ($folder && $folder !== '.') {
            Storage::disk('public')->deleteDirectory($folder);
        } else {
            Storage::disk('public')->delete($relative);
        }
    }

    private function renamePdfFolder(TransparencyDocument $document, string $oldSlug): void
    {
        if (! $document->file_path || ! Str::startsWith($document->file_path, '/storage/')) {
            return;
        }

        $oldFolder = 'transparency/'.$oldSlug;
        $newFolder = 'transparency/'.$document->slug;

        if (Storage::disk('public')->exists($oldFolder)) {
            Storage::disk('public')->move($oldFolder, $newFolder);
            $document->file_path = '/storage/'.$newFolder.'/'.$document->slug.'.pdf';
        }
    }
}
