<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Category;
use App\Models\Post;
use Closure;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Str;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationGroup = 'Blog';

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?int $navigationSort = 3;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()->schema([
                    Select::make('category_id')
                        ->label('Cateogry')
                        ->options(Category::all()->pluck('name', 'id'))
                        ->searchable(),
                    TextInput::make('title')
                        ->reactive()
                        ->afterStateUpdated(function (Closure $set,$state){
                            $set('slug',Str::slug($state));
                        })->required(),
                    TextInput::make('slug')->required(),
                    FileUpload::make('image')
                        ->disk('local')
                        ->directory('public/image/post'),
                    RichEditor::make('content'),
                    Toggle::make("is_published"),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('title')->limit('5'),
                ImageColumn::make('image')->visibility('private'),
                BooleanColumn::make('is_published'),
            ])
            ->filters([
                Filter::make('is_published')->label('Published')
                        ->query(fn (Builder $query): Builder => $query->where('is_published', true)),
                SelectFilter::make('category_id')
                        ->options([
                            1 => 'Tailwind',
                        ]),
                Filter::make('created_at')
                ->form([
                    DatePicker::make('created_until')->default(now())
                ])
                ->query(fn (Builder $query, array $data): Builder => $query->whereDate('created_at','>=',$data)),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
