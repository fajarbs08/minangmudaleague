<?php

namespace App\Http\Controllers;

use App\Models\InformationResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InformationResourceController extends Controller
{
    public function index()
    {
        $category = request()->string('category')->value();
        $search = request()->string('search')->value();
        $sort = request()->string('sort')->value() ?: 'created_at';
        $direction = request()->string('direction')->value() === 'asc' ? 'asc' : 'desc';

        $resources = InformationResource::query()
            ->when($category, fn ($query, $value) => $query->where('category', $value))
            ->when($search, fn ($query, $value) => $query->where('title', 'like', "%{$value}%"))
            ->get();

        $resources = match ($sort) {
            'file_size' => $direction === 'asc'
                ? $resources->sortBy(fn (InformationResource $item) => $item->file_size_bytes ?? -1)
                : $resources->sortByDesc(fn (InformationResource $item) => $item->file_size_bytes ?? -1),
            'created_at' => $direction === 'asc'
                ? $resources->sortBy('created_at')
                : $resources->sortByDesc('created_at'),
            default => $resources
                ->sortByDesc('is_pinned')
                ->sortBy('sort_order')
                ->sortByDesc('created_at'),
        };

        $resources = $resources->values();

        return view('pages/information-resources/index', [
            'title' => 'Pusat Informasi',
            'resources' => $resources,
            'activeCategory' => $category,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
            'resource' => new InformationResource([
                'category' => 'other',
                'sort_order' => InformationResource::max('sort_order') + 1,
                'is_pinned' => false,
                'is_published' => true,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $file = $request->file('attachment');

        $data['file_path'] = $file->store('information-resources', 'public');
        $data['file_name'] = $file->getClientOriginalName();
        $data['file_mime'] = $file->getClientMimeType();

        InformationResource::create($data);

        return redirect()->route('information-resources.index')->with('status', 'Dokumen pusat informasi berhasil ditambahkan.');
    }

    public function edit(InformationResource $informationResource)
    {
        return view('pages/information-resources/edit', [
            'title' => 'Edit Pusat Informasi',
            'resource' => $informationResource,
        ]);
    }

    public function update(Request $request, InformationResource $informationResource)
    {
        $data = $this->validatedData($request, true);

        if ($request->hasFile('attachment')) {
            Storage::disk('public')->delete($informationResource->file_path);

            $file = $request->file('attachment');
            $data['file_path'] = $file->store('information-resources', 'public');
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_mime'] = $file->getClientMimeType();
        }

        $informationResource->update($data);

        return redirect()->route('information-resources.index')->with('status', 'Dokumen pusat informasi berhasil diperbarui.');
    }

    public function destroy(InformationResource $informationResource)
    {
        Storage::disk('public')->delete($informationResource->file_path);
        $informationResource->delete();

        return redirect()->route('information-resources.index')->with('status', 'Dokumen pusat informasi berhasil dihapus.');
    }

    public function download(InformationResource $informationResource)
    {
        return Storage::disk('public')->download($informationResource->file_path, $informationResource->file_name);
    }

    public function togglePublish(InformationResource $informationResource)
    {
        $informationResource->update([
            'is_published' => !$informationResource->is_published,
        ]);

        return redirect()
            ->route('information-resources.index', request()->only(['category', 'search', 'sort', 'direction']))
            ->with('status', 'Status tampil dokumen berhasil diperbarui.');
    }

    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'selected_ids' => ['required', 'array', 'min:1'],
            'selected_ids.*' => ['integer', 'exists:information_resources,id'],
            'bulk_action' => ['required', 'in:publish,unpublish,pin,unpin,delete'],
        ]);

        $resources = InformationResource::query()
            ->whereIn('id', $validated['selected_ids'])
            ->get();

        if ($validated['bulk_action'] === 'delete') {
            $resources->each(function (InformationResource $resource) {
                Storage::disk('public')->delete($resource->file_path);
                $resource->delete();
            });

            return redirect()
                ->route('information-resources.index', $request->only(['category', 'search', 'sort', 'direction']))
                ->with('status', $resources->count().' dokumen berhasil dihapus.');
        }

        $updates = match ($validated['bulk_action']) {
            'publish' => ['is_published' => true],
            'unpublish' => ['is_published' => false],
            'pin' => ['is_pinned' => true],
            'unpin' => ['is_pinned' => false],
        };

        InformationResource::query()
            ->whereIn('id', $resources->pluck('id'))
            ->update($updates);

        return redirect()
            ->route('information-resources.index', $request->only(['category', 'search', 'sort', 'direction']))
            ->with('status', $resources->count().' dokumen berhasil diperbarui.');
    }

    private function validatedData(Request $request, bool $isUpdate = false): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:template,flow,rules,manual,other'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_pinned' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
            'attachment' => [$isUpdate ? 'nullable' : 'required', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:2048'],
        ]);
    }
}
