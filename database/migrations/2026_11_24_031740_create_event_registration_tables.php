<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('attendee')->index();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizer_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('cover_image')->nullable();
            $table->string('venue');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->dateTime('registration_opens_at')->nullable();
            $table->dateTime('registration_closes_at')->nullable();
            $table->string('currency', 3)->default('PHP');
            $table->string('status')->default('draft')->index();
            $table->timestamps();
            $table->index(['status', 'starts_at']);
        });

        Schema::create('ticket_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->unsignedInteger('capacity');
            $table->unsignedInteger('sold')->default(0);
            $table->unsignedInteger('minimum_per_order')->default(1);
            $table->unsignedInteger('maximum_per_order')->default(10);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['event_id', 'is_active']);
        });

        Schema::create('add_ons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->unsignedInteger('capacity')->nullable();
            $table->unsignedInteger('sold')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['event_id', 'is_active']);
        });

        Schema::create('discount_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('type');
            $table->decimal('amount', 10, 2);
            $table->decimal('minimum_order', 10, 2)->default(0);
            $table->unsignedInteger('maximum_redemptions')->nullable();
            $table->unsignedInteger('redemption_count')->default(0);
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->constrained()->restrictOnDelete();
            $table->foreignId('discount_code_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reference')->unique();
            $table->string('attendee_name');
            $table->string('attendee_email');
            $table->decimal('ticket_subtotal', 10, 2);
            $table->decimal('add_on_subtotal', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->string('status')->default('confirmed');
            $table->timestamps();
            $table->index(['user_id', 'event_id']);
        });

        Schema::create('registration_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ticket_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ticket_name');
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('line_total', 10, 2);
            $table->timestamps();
        });

        Schema::create('registration_add_ons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('add_on_id')->nullable()->constrained()->nullOnDelete();
            $table->string('add_on_name');
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('line_total', 10, 2);
            $table->timestamps();
        });

        Schema::create('issued_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('registration_item_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->string('status')->default('valid');
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamps();
        });

        Schema::create('discount_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discount_code_id')->constrained()->cascadeOnDelete();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['discount_code_id', 'registration_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_redemptions');
        Schema::dropIfExists('issued_tickets');
        Schema::dropIfExists('registration_add_ons');
        Schema::dropIfExists('registration_items');
        Schema::dropIfExists('registrations');
        Schema::dropIfExists('discount_codes');
        Schema::dropIfExists('add_ons');
        Schema::dropIfExists('ticket_types');
        Schema::dropIfExists('events');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
