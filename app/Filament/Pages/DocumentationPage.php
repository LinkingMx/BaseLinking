<?php

namespace App\Filament\Pages;

use App\Models\ManualSection;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\Actions;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Pages\Page;
use Filament\Support\Enums\FontWeight;
use Illuminate\Contracts\Support\Htmlable;

class DocumentationPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Documentación';
    protected static ?string $title = 'Manual de Usuario';
    protected static ?string $slug = 'documentacion';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup = 'Ayuda';

    protected static string $view = 'filament.pages.documentation-page';

    public ?string $selectedCategory = null;
    public ?string $searchTerm = null;
    public ?int $selectedSection = null;

    public function mount(): void
    {
        // Set default to first section if none selected
        if (!$this->selectedSection) {
            $firstSection = ManualSection::active()->orderBy('sort_order')->first();
            $this->selectedSection = $firstSection?->id;
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Grid::make(3)
                    ->schema([
                        TextInput::make('searchTerm')
                            ->label('Buscar en documentación')
                            ->placeholder('Buscar por título o contenido...')
                            ->live(debounce: 500)
                            ->afterStateUpdated(fn () => $this->resetSelection()),

                        Select::make('selectedCategory')
                            ->label('Categoría')
                            ->placeholder('Todas las categorías')
                            ->options(ManualSection::getCategoryOptions())
                            ->live()
                            ->afterStateUpdated(fn () => $this->resetSelection()),

                        Select::make('selectedSection')
                            ->label('Sección')
                            ->placeholder('Seleccionar sección')
                            ->options($this->getSectionOptions())
                            ->live()
                            ->searchable(),
                    ]),
            ]);
    }

    protected function getSectionOptions(): array
    {
        $query = ManualSection::active()->orderBy('sort_order');

        if ($this->selectedCategory) {
            $query->where('category', $this->selectedCategory);
        }

        if ($this->searchTerm) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('content', 'like', '%' . $this->searchTerm . '%');
            });
        }

        return $query->pluck('title', 'id')->toArray();
    }

    protected function resetSelection(): void
    {
        $this->selectedSection = null;
        $firstSection = $this->getFilteredSections()->first();
        if ($firstSection) {
            $this->selectedSection = $firstSection->id;
        }
    }

    protected function getFilteredSections()
    {
        $query = ManualSection::active()->orderBy('sort_order');

        if ($this->selectedCategory) {
            $query->where('category', $this->selectedCategory);
        }

        if ($this->searchTerm) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('content', 'like', '%' . $this->searchTerm . '%');
            });
        }

        return $query->get();
    }

    public function sectionsListInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record(new ManualSection()) // Dummy record for structure
            ->schema([
                Section::make('Secciones Disponibles')
                    ->icon('heroicon-o-list-bullet')
                    ->schema([
                        \Filament\Infolists\Components\Grid::make(1)
                            ->schema(
                                $this->getFilteredSections()->map(function (ManualSection $section) {
                                    return Section::make($section->title)
                                        ->description($section->description)
                                        ->schema([
                                            TextEntry::make('category_' . $section->id)
                                                ->label('Categoría')
                                                ->state(ManualSection::getCategoryOptions()[$section->category] ?? $section->category)
                                                ->badge()
                                                ->color('primary'),
                                                
                                            Actions::make([
                                                Action::make('select_' . $section->id)
                                                    ->label('Ver sección')
                                                    ->icon('heroicon-o-eye')
                                                    ->button()
                                                    ->action(function () use ($section) {
                                                        $this->selectedSection = $section->id;
                                                    })
                                            ])
                                        ])
                                        ->collapsible()
                                        ->collapsed(true);
                                })->toArray()
                            )
                    ])
                    ->collapsible()
                    ->collapsed(false)
            ]);
    }

    public function selectedSectionInfolist(Infolist $infolist): Infolist
    {
        $section = $this->selectedSection ? ManualSection::find($this->selectedSection) : null;

        if (!$section) {
            return $infolist
                ->record(new ManualSection())
                ->schema([
                    Section::make('Selecciona una sección')
                        ->icon('heroicon-o-document-text')
                        ->schema([
                            TextEntry::make('placeholder')
                                ->label('')
                                ->state('Por favor, selecciona una sección del manual para ver su contenido.')
                        ])
                ]);
        }

        return $infolist
            ->record($section)
            ->schema([
                Section::make('Contenido del Manual')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        \Filament\Infolists\Components\Grid::make(2)
                            ->schema([
                                TextEntry::make('title')
                                    ->label('Título')
                                    ->weight(FontWeight::Bold)
                                    ->size('lg'),

                                TextEntry::make('difficulty_level')
                                    ->label('Nivel de Dificultad')
                                    ->badge()
                                    ->formatStateUsing(fn (string $state): string => 
                                        ManualSection::getDifficultyLevels()[$state] ?? $state
                                    )
                                    ->color(fn (string $state): string => match ($state) {
                                        'beginner' => 'success',
                                        'intermediate' => 'warning',
                                        'advanced' => 'danger',
                                        default => 'gray',
                                    }),

                                TextEntry::make('category')
                                    ->label('Categoría')
                                    ->badge()
                                    ->formatStateUsing(fn (string $state): string => 
                                        ManualSection::getCategoryOptions()[$state] ?? $state
                                    )
                                    ->color('primary'),

                                TextEntry::make('tags')
                                    ->label('Etiquetas')
                                    ->badge()
                                    ->separator(',')
                                    ->color('gray'),
                            ]),

                        TextEntry::make('description')
                            ->label('Descripción')
                            ->columnSpanFull(),

                        Section::make('Contenido')
                            ->schema([
                                TextEntry::make('content')
                                    ->label('')
                                    ->html()
                                    ->columnSpanFull(),
                            ])
                            ->collapsible()
                            ->collapsed(false),

                        \Filament\Infolists\Components\Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Creado')
                                    ->dateTime(),

                                TextEntry::make('updated_at')
                                    ->label('Actualizado')
                                    ->dateTime(),
                            ]),
                    ])
            ]);
    }

    public function getTitle(): string|Htmlable
    {
        return 'Manual de Usuario';
    }

    public function getHeading(): string|Htmlable
    {
        return 'Documentación del Sistema';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Encuentra toda la información que necesitas para usar el sistema de manera efectiva.';
    }
}
