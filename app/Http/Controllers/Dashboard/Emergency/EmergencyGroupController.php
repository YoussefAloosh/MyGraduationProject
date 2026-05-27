<?php

namespace App\Http\Controllers\Dashboard\Emergency;

use App\Http\Controllers\Controller;
use App\Http\Requests\Emergency\StoreEmergencyGroupRequest;
use App\Http\Requests\Emergency\UpdateEmergencyGroupRequest;
use App\Http\Resources\Emergency\EmergencyGroupCollection;
use App\Http\Resources\Emergency\EmergencyGroupResource;
use App\Models\EmergencyGroup;
use Illuminate\Http\Request;

class EmergencyGroupController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', EmergencyGroup::class);

        $groups = EmergencyGroup::query()
            ->with('creator')
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->status === 'active',   fn($q) => $q->active())
            ->when($request->status === 'inactive', fn($q) => $q->inactive())
            ->latest()
            ->paginate($request->per_page ?? 10);

        return new EmergencyGroupCollection($groups);
    }

    public function show(EmergencyGroup $emergencyGroup)
    {
        $this->authorize('view', $emergencyGroup);

        $emergencyGroup->load('creator');

        return new EmergencyGroupResource($emergencyGroup);
    }

    public function store(StoreEmergencyGroupRequest $request)
    {
        $this->authorize('create', EmergencyGroup::class);

        $group = EmergencyGroup::create([
            ...$request->validated(),
            'radius_km'  => $request->validated()['radius_km'] ?? 5,
            'created_by' => auth()->id(),
            'is_active'  => true,
        ]);

        return new EmergencyGroupResource($group->load('creator'));
    }

    public function update(UpdateEmergencyGroupRequest $request, EmergencyGroup $emergencyGroup)
    {
        $this->authorize('update', $emergencyGroup);

        $emergencyGroup->update($request->validated());

        return new EmergencyGroupResource($emergencyGroup->load('creator'));
    }

    public function destroy(EmergencyGroup $emergencyGroup)
    {
        $this->authorize('delete', $emergencyGroup);

        $emergencyGroup->delete();

        return response()->json(['message' => 'Group deleted successfully.']);
    }

    public function toggleActive(EmergencyGroup $emergencyGroup)
    {
        $this->authorize('toggleActive', $emergencyGroup);

        $emergencyGroup->update(['is_active' => ! $emergencyGroup->is_active]);

        return new EmergencyGroupResource($emergencyGroup->load('creator'));
    }
}
