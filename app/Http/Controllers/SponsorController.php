<?php

namespace App\Http\Controllers;

use App\Models\Sponsor;
use App\Services\ImageAssetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SponsorController extends Controller
{
    public function __construct(private ImageAssetService $imageAssetService)
    {
    }

    public function index()
    {
        $search = request()->string('search')->value();
        $sponsors = Sponsor::query()
            ->when($search, fn ($query, $value) => $query->where('name', 'like', "%{$value}%"))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('pages.sponsors.index', [
            'title' => 'Sponsor',
            'sponsors' => $sponsors,
            'search' => $search,
            'sponsor' => new Sponsor([
                'tier' => 'Partner',
                'sort_order' => (Sponsor::max('sort_order') ?? 0) + 1,
                'is_published' => true,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $data['logo_path'] = $this->imageAssetService->storeLogo($request->file('logo'), 'sponsors');

        Sponsor::create($data);

        return redirect()->route('sponsors.index')->with('status', 'Sponsor berhasil ditambahkan.');
    }

    public function edit(Sponsor $sponsor)
    {
        return view('pages.sponsors.edit', [
            'title' => 'Edit Sponsor',
            'sponsor' => $sponsor,
        ]);
    }

    public function update(Request $request, Sponsor $sponsor)
    {
        $data = $this->validatedData($request, true);

        if ($request->hasFile('logo')) {
            Storage::disk('public')->delete($sponsor->logo_path);
            $data['logo_path'] = $this->imageAssetService->storeLogo($request->file('logo'), 'sponsors');
        }

        $sponsor->update($data);

        return redirect()->route('sponsors.index')->with('status', 'Sponsor berhasil diperbarui.');
    }

    public function destroy(Sponsor $sponsor)
    {
        Storage::disk('public')->delete($sponsor->logo_path);
        $sponsor->delete();

        return redirect()->route('sponsors.index')->with('status', 'Sponsor berhasil dihapus.');
    }

    private function validatedData(Request $request, bool $isUpdate = false): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'short_name' => ['nullable', 'string', 'max:50'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'tier' => ['required', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_published' => ['nullable', 'boolean'],
            'logo' => [$isUpdate ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:512'],
        ]);

        $data['is_published'] = $request->boolean('is_published');

        return $data;
    }
}
