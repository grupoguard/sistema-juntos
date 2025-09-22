<div>
    <label for="group">Selecione a cooperativa:<span class="text-danger">*</span></label>
    <select wire:model.live="group_id" id="group" class="form-control">
        <option value="">Selecione</option>
        @foreach($groups as $group)
            <option value="{{ $group->id }}">{{ $group->name }}</option>
        @endforeach
    </select>
</div>