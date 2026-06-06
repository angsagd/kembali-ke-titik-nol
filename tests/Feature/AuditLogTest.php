<?php

use App\Models\AuditLog;
use App\Models\MediaItem;
use App\Models\User;

test('audit logs can record important user activity', function () {
    $user = User::factory()->create();
    $mediaItem = MediaItem::factory()->photo()->create(['title' => 'Foto Reuni']);

    $this->actingAs($user);

    $auditLog = AuditLog::record(
        action: 'media.uploaded',
        entity: $mediaItem,
        newValues: ['title' => 'Foto Reuni'],
    );

    expect($auditLog->user_id)->toBe($user->id);
    expect($auditLog->action)->toBe('media.uploaded');
    expect($auditLog->entity_type)->toBe($mediaItem->getMorphClass());
    expect($auditLog->entity_id)->toBe($mediaItem->id);
    expect($auditLog->new_values)->toBe(['title' => 'Foto Reuni']);
    expect($auditLog->created_at)->not->toBeNull();
});
