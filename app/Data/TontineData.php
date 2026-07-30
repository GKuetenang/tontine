<?php

namespace App\Data;

use App\Models\Tontine;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Mimes;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Optional;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'Tontine')]
class TontineData extends Data
{
    public function __construct(
        public string $name,
        public string $slug,
        public string $member_number_prefix,
        public Optional|CarbonImmutable $created_at,
        public Optional|CarbonImmutable $updated_at,
        public Optional|Lazy|string $image,
        #[Mimes('jpg,jpeg,png,webp'), Max(2048)]
        public Optional|UploadedFile $image_file,
        public Optional|int $id,
        public Optional|int $members_count,
        public Optional|string $currency = 'XAF',
        public Optional|bool $is_active = true,
        public Optional|bool $is_public = false,
        public Optional|bool $is_verified = false,
        public ?string $description = null,
    ) {}

    public static function fromModel(Tontine $tontine): self
    {
        return self::from(
            $tontine,
            [
                'image' => Lazy::whenLoaded(
                    'media',
                    $tontine,
                    fn() => $tontine->getFirstMediaUrl('image')
                ),
            ]
        );
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
                Rule::unique('tontines', 'name')
                    ->where(
                        function ($query) use ($request) {
                            return $query->where('user_id', $request->user()->id);
                        }
                    )->ignore($request->route()->parameter('tontine')),
            ],
            'slug' => [
                '
                nullable',
                'string',
                Rule::unique('tontines', 'slug')
                    ->ignore($request->route()->parameter('tontine')),
            ],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public static function authorize(Request $request): bool
    {
        $tontine = $request->route('tontine');

        if ($tontine instanceof Tontine) {
            return $request->user()->can('update', $tontine);
        }

        return $request->user()->can('create', Tontine::class);
    }

    public static function prepareForPipeline(array $properties): array
    {
        if (empty($properties['slug']) && !empty($properties['name'])) {
            $properties['slug'] = \Str::slug($properties['name']);
        }

        if (!empty($properties['member_number_prefix'])) {
            $properties['member_number_prefix'] = \Str::upper($properties['member_number_prefix']);
        }

        return $properties;
    }
}
