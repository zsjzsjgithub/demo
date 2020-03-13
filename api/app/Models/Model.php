<?php
/**
 * Description
 *
 * -
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Concerns\BuildsQueries;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder as QBuilder;

/**
 * @property int $id 唯一主键
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 * @property array $attributes
 *
 * @method static bool each(callable $callback, int $count = 1000) Execute a callback over each item while chunking.
 * @method static Eloquent|static|object|BuildsQueries|null first(array $columns = ['*']) Execute the query and get the first result.
 * @method static mixed|Builder when(mixed $value, callable $callback, callable $default = null) Apply the callback's query changes if the given "value" is true.
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginator(Collection $items, int $total, int $perPage, int $currentPage, array $options) Create a new length-aware paginator instance.
 * @method static Builder|static has(string $relation, string $operator = '>=', int $count = 1, string $boolean = 'and', \Closure $callback = null) Add a relationship count / exists condition to the query.
 * @method static Builder|static whereHas(string $relation, \Closure $callback = null, string $operator = '>=', int $count = 1) Add a relationship count / exists condition to the query with where clauses.
 * @method static Builder|static orWhereHas(string $relation, \Closure $callback = null, string $operator = '>=', int $count = 1) Add a relationship count / exists condition to the query with where clauses and an "or".
 * @method static Builder|static doesntHave(string $relation, string $boolean = 'and', \Closure $callback = null) Add a relationship count / exists condition to the query.
 * @method static Eloquent|static make(array $attributes = []) Create and return an un-saved model instance.
 * @method static Builder|static where(string | array | \Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and') Add a basic where clause to the query.
 * @method static Builder|static orWhere(\Closure | array | string $column, mixed $operator = null, mixed $value = null) Add an "or where" clause to the query.
 * @method static Collection|Builder[]|null|Builder|Eloquent|static|static[] find(mixed $id, array $columns = ['*']) Find a model by its primary key.
 * @method static Collection findMany(Arrayable | array $ids, $columns = ['*']) Find multiple models by their primary keys.
 * @method static Collection|Builder[]|null|Builder|Eloquent|static|static[] findOrFail(mixed $id, array $columns = ['*']) Find a model by its primary key or throw an exception.
 * @method static Builder|Eloquent|static firstOrCreate(array $attributes, array $values = []) Get the first record matching the attributes or create it.
 * @method static Builder|Eloquent|static updateOrCreate(array $attributes, array $values = []) Create or update a record matching the attributes, and fill it with values.
 * @method static mixed value(string $column) Get a single column's value from the first result of a query.
 * @method static Collection|Builder[] get(array $columns = ['*']) Execute the query as a "select" statement.
 * @method static int count(string $columns = '*') Retrieve the "count" result of the query.
 * @method static mixed sum(string $column) Retrieve the sum of the values of a given column.
 * @method static \Illuminate\Support\Collection pluck(string $column, string | null $key = null) Get an array with the values of a given column.
 * @method static LengthAwarePaginator paginate(int $perPage, array $columns = ['*'], string $pageName = 'page', int | null $page = null) Paginate the given query.
 * @method static Builder|Eloquent|static create(array $attributes = []) Save a new model and return the instance.
 * @method static int update(array $values) Update a record in the database.
 * @method static int increment(string $column, float | int $amount = 1, array $extra = []) Increment a column's value by a given amount.
 * @method static int decrement(string $column, float | int $amount = 1, array $extra = []) Decrement a column's value by a given amount.
 * @method static mixed delete() Delete a record from the database.
 * @method static mixed forceDelete() Run the default delete function on the builder.
 * @method static Builder with(mixed ...$relations) Set the relationships that should be eager loaded.
 * @method static Builder withCount(mixed ...$relations) Add subselect queries to count the relations.
 * @method static Builder without(mixed $relations) Prevent the specified relations from being eager loaded.
 * @method static QBuilder getQuery() Get the underlying query builder instance.
 * @method static QBuilder select(array | mixed $columns = ['*']) Set the columns to be selected.
 * @method static QBuilder|QBuilder selectRaw(string $expression, array $bindings = []) Add a new "raw" select expression to the query.
 * @method static QBuilder|Builder whereIn(string $column, mixed $values, string $boolean = 'and', bool $not = false) Add a "where in" clause to the query.
 * @method static QBuilder|Builder orWhereIn(string $column, mixed $values) Add an "or where in" clause to the query.
 * @method static QBuilder|Builder whereDate(string $column, string $operator, \DateTimeInterface | string $value = null, string $boolean = 'and') Add a "where date" statement to the query.
 * @method static QBuilder groupBy(array ...$groups) Add a "group by" clause to the query.
 * @method static QBuilder orderBy(string $column, string $direction = 'asc') Add an "order by" clause to the query.
 * @method static QBuilder orderByDesc(string $column) Add a descending "order by" clause to the query.
 * @method static QBuilder limit(int $value) Set the "limit" value of the query.
 * @method static Builder|static withoutGlobalScope(Scope | string $scope) Remove a registered global scope.
 * @method static Builder|static withTrashed()
 */
class Model extends Eloquent
{
    use SoftDeletes;

    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at', 'password'];

    protected $dates = [
        'deleted_at',
    ];

    protected $hidden = [
        'deleted_at',
    ];

}
