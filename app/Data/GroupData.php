<?php

namespace App\Data;

use App\Models\Group;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Mimes;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'Group')]
class GroupData extends Data
{
    public function __construct(
        public string $name,
        public Optional|string $slug,
        public string $member_number_prefix,
        public string $default_loan_interest_rate,
        public int $default_loan_term_months,
        #[WithTransformer(
            DateTimeInterfaceTransformer::class,
            format: 'Y-m-d\TH:i:s',
        )]
        public Optional|CarbonImmutable $created_at,
        #[WithTransformer(
            DateTimeInterfaceTransformer::class,
            format: 'Y-m-d\TH:i:s',
        )]
        public Optional|CarbonImmutable $updated_at,
        public Optional|Lazy|string $image,
        #[Mimes('jpg,jpeg,png,webp'), Max(2048)]
        #[LiteralTypeScriptType('File')]
        public Optional|UploadedFile $image_file,
        public Optional|GroupAbilitiesData $can,
        public Optional|int|null $default_contribution_amount,
        public Optional|int $id,
        public Optional|int $members_count,
        public Optional|int $sessions_count,
        public Optional|string $currency = 'XAF',
        public Optional|bool $is_active = true,
        public Optional|bool $is_public = false,
        public Optional|bool $is_verified = false,
        public ?string $description = null,
    ) {}

    public static function fromModel(
        Group $group,
        GroupAbilitiesData|Optional|null $can = null,
    ): self {
        return self::from([
            ...$group->only([
                'id',
                'name',
                'slug',
                'member_number_prefix',
                'default_contribution_amount',
                'default_loan_interest_rate',
                'default_loan_term_months',
                'currency',
                'description',
                'is_active',
                'is_public',
                'is_verified',
                'created_at',
                'updated_at',
            ]),
            'members_count' => $group->members_count ?? 0,
            'sessions_count' => $group->sessions_count ?? 0,
            'image' => Lazy::whenLoaded(
                'media',
                $group,
                fn (): ?string => $group->getFirstMediaUrl(),
            ),
            'image_file' => Optional::create(),
            'can' => $can ?? Optional::create(),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public static function rules(Request $request): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:200',
                Rule::unique('groups', 'name')
                    ->where(
                        function ($query) use ($request) {
                            return $query->where('user_id', $request->user()->id);
                        }
                    )->ignore($request->route()->parameter('group')),
            ],
            'default_loan_interest_rate' => ['required', 'decimal:0,2', 'gte:0', 'lte:100'],
            'default_loan_term_months' => ['required', 'integer', 'min:1', 'max:120'],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public static function authorize(Request $request): bool
    {
        $group = $request->route('group');

        if ($group instanceof Group) {
            return $request->user()->can('update', $group);
        }

        return $request->user()->can('create', Group::class);
    }

    public static function prepareForPipeline(array $properties): array
    {
        // if (empty($properties['slug']) && !empty($properties['name'])) {
        //     $properties['slug'] = \Str::slug($properties['name']);
        // }

        if (! empty($properties['member_number_prefix'])) {
            $properties['member_number_prefix'] = \Str::upper($properties['member_number_prefix']);
        }

        return $properties;
    }
}
