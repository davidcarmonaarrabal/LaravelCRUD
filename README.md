# CÓMO HACER EL PROYECTO:

## 1. CREAMOS EL PROYECTO
Bien, empezamos a crear el proyecto, ya sabemos `laravel new examen`, seleccionando dentro `Laravel Breeze`, `Livewire (Volt Class API) with Alpine`, `PHPUnit` y `MySQL`.

Ahora **Cargamos nuestros datos de nuestra BBDD** en `.env`, en mi caso:

```
DB_HOST=127.0.0.1 
DB_PORT=3306 #(Puerto que usa MySQL)
DB_DATABASE=proyecto1 #(Nombre de la base de datos que usará el proyecto, normalmente igual que el nombre del proyecto)
DB_USERNAME=root 
DB_PASSWORD=david 
```

Bien, tras esto, cargamos las migraciones con `php artisan serve` y en otra terminal `php artisan migrate`, **seleccionando SÍ a crear la BBDD**.

## 2. CREAMOS EL MODELO Y MIGRACIONES
En este caso, vamos a usar dos tablas, ***users*** y ***diets***, como el de **users** viene creado, creamos el de **diets**, en mi caso `php artisan make:model Diet -m`, con el ***-m***, también nos genera las ***Migraciones***, las cuales vamos a empezar a editar para dar estructura a la tabla de la **BBDD**, primero con la de *Diet* en `database/migrations/...`:

```
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diets', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('description');
            $table->integer('totalCalories');
            $table->date('fecha')->default(now());
            $table->timestamps();
            $table->unsignedBigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diets');
    }
};
```

**Remarcar** el `$table->date('fecha')->default(now());`, que nos ***coge la fecha actual*** y también el `$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');` que nos define la ***Foreign Key*** y su **forma de ser borrada**.

Recordar importar, en este caso, el `use Illuminate\Support\Facades\DB;` y el `use Illuminate\Support\Facades\Schema;`, aunque todo depende de la situación. Ahora vamos con la migración de **users**:

```
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('surname');
            $table->string('telefono')->default("mamawebo");
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
```

**Mucho de todo esto ya viene hecho**, realmente nosotros **sólo editamos los datos de la tabla**.

Ahora podemos proceder con los ***Modelos***, primero el de `app/Models/Diet.php`:

```
class Diet extends Model
{
    use SoftDeletes, HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

**IMPORTA TODO LO NECESARIO**, como el ***SoftDeletes***.

A parte, aquí establecemos la **relación** de la tabla **Diets** con **Users**, en este caso, un `public function user(): BelongsTo`, que es un ***1:1***, que significa que *Un usuario puede tener muchas dietas, pero una dieta sólo puede ser de un usuario*.

Y ahora vamos con `app/Models/User.php`:

```
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'surname',
        'telefono',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function diets(): HasMany
    {
        return $this->hasMany(Diet::class, 'user_id', 'id');
    }
}
```

Aquí, remarcamos varias cosas:
* `use HasFactory, Notifiable, SoftDeletes;`: **importa lo necesario**.
* `protected $fillable = [`: son los atributos que son rellenables en masa en la BBDD.
* `protected $hidden = [`: son los atributos que no serán mostrados.
* `public function diets(): HasMany`: ***1:N***, *un usuario tiene una o más dietas*.

## 3. CREAMOS LOS SEEDERS
Ahora, *el ejercicio nos pide meter un usuario y una dieta de forma manual* usando ***los seeders***, bien, nos vamos a `database/seeders/DietSeeder.php`, pero antes los creamos con `php artisan make:seed UserSeeder` y `php artisan make:seed DietSeeder`:

```
class DietSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('diets')->insert([
            'title' => 'Dieta 1',
            'description' => 'Descripción de la dieta 1',
            'totalCalories' => 2000,
            'user_id' => 1
        ]);

        Diet::factory(3)->create();
    }
}
```

Aquí decimos dos cosas, *que cuando se ejecuten las migraciones*, se **cree** la ***Dieta1*** que hemos especificado ahí, y luego, que ***llame a la factoria*** para **crear 3 dietas más**, cosa que veremos más adelante.

Pasamos a `database/seeders/UserSeeder.php`:

```
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'name' => 'Admin',
            'surname' => 'Administrador',
            'telefono' => '123456789',
            'email' => 'admin@example.com',
            'password' => bcrypt('password')
        ]);

        User::factory(3)->create();
    }
    
}
```

Bien, lo mismo en esta migración, ahora sólo resta ir a `database/seeders/DatabaseSeeder.php` para decirle *que llame a las dos migraciones anteriores*:

```
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call(UserSeeder::class);
        $this->call(DietSeeder::class);
        
    }
}

```

Con esto, podríamos ejecutar `php artisan migrate:fresh --seed`, pero esto en este caso ***nos daría error***, ya que ya hemos *incluido la lógica de las factorías*, que aún no hemos especificado, pero, *si comentamos las líneas de las factorías*, deberiamos poder ejecutar el comando, y en nuestra **BBDD** ya se verían reflejados esos datos.

## 4. CREAMOS LAS FACTORÍAS

Las creamos con `php artisan make:factory DietFactory` y `php artisan make:factory UserFactory`, ahora, nos vamos a `database/factories/DietFactory.php`:

```
class DietFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->name(),
            'description' => $this->faker->text(),
            'totalCalories' => $this->faker->randomNumber(4),
            'user_id' => 1
        ];
    }
}
```

Con esto, pedimos que inserte en la **BBDD** usando un ***fake*** en las *columnas seleccionadas*, y ahora psamos a `database/factories/UserFactory.php`:

```
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'surname' => fake()->name(),
            'telefono' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

   

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
```

Lo mismo que antes.

## 5. PASAMOS A LOS COMPONENTES:
Vale, pasamos a lo interesante, una vez creado todo lo anterior, podemos empezar a *mostrar esos datos por pantalla*, para ello, nos vamos a centrar en mostrarlos en la *Dashboard*, cosas a tener en cuenta es comprobar antes de todo que esa página **usa bien los estilos**, ya que sino, tendremos que usar el comando `npm run dev` para activarlos.

Bien, ahora, antes de mostrar nada por pantalla, vamos a crear ***los controladores***, ejecutamos `php artisan make:livewire DietsTable`, aunque en mi caso, lo he llamado **DietsTable**, deberiamos añadirle al final un **Component**.

Esto nos creará `app/Livewire/DietsTable.php`, en el que añadimos:

```
class DietsTable extends Component
{
    public $diets = [];
    public $title;
    public $fecha;
    public $modal = false;

    public function mount() {
        $this->diets = Diet::where('user_id', Auth::id())->get();
    }
    public function render()
    {
        return view('livewire.diets-table');
    }

    private function clearFields(){
        $this->title = '';
        $this->fecha = '';
    }

    private function createDiet(){
        $this->clearFields();
        $this->modal = true;
    }
}
```

Aquí definimos lo siguiente:
* `public function mount() {`: que monta el modelo.
* `public function render()`: lo renderiza.
* `private function clearFields(){`: limpia los campos.
* `private function createDiet(){`: crea la dieta.

Ahora, nos vamos a `resources/livewire/diets-table.blade.php` y añadimos la lógica ***HTML*** que es lo que usaremos luego en el resto de vistas para **mostrar la tabla en la web, con sus funcionalidades**:

```
<section class="py-1 bg-blueGray-50">
<div class="w-full xl:w-8/12 mb-12 xl:mb-0 px-4 mx-auto mt-24">
  <div class="relative flex flex-col min-w-0 break-words bg-white w-full mb-6 shadow-lg rounded ">
    <div class="rounded-t mb-0 px-4 py-3 border-0">
      <div class="flex flex-wrap items-center">
        <div class="relative w-full px-4 max-w-full flex-grow flex-1">
          <h3 class="font-semibold text-base text-blueGray-700">Dietas de {{ Auth::user()->name }}</h3>
        </div>
        <div class="relative w-full px-4 max-w-full flex-grow flex-1 text-right">
          <button wire:click="openModal(null, true)" class="bg-indigo-500 text-white active:bg-indigo-600 text-xs font-bold uppercase px-3 py-1 rounded outline-none focus:outline-none mr-1 mb-1 ease-linear transition-all duration-150" type="button">Añadir dieta</button>
        </div>
      </div>
    </div>

    <div class="block w-full overflow-x-auto">
      <table class="items-center bg-transparent w-full border-collapse ">
        <thead>
          <tr>
            <th class="px-6 bg-blueGray-50 text-blueGray-500 align-middle border border-solid border-blueGray-100 py-3 text-xs uppercase border-l-0 border-r-0 whitespace-nowrap font-semibold text-left">
              Título
            </th>
            <th class="px-6 bg-blueGray-50 text-blueGray-500 align-middle border border-solid border-blueGray-100 py-3 text-xs uppercase border-l-0 border-r-0 whitespace-nowrap font-semibold text-left">
              Fecha
            </th>
            <th></th>
          </tr>
        </thead>

        <tbody>
          @foreach ($diets as $diet)
            <tr>
              <th class="border-t-0 px-6 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left text-blueGray-700 ">
                {{$diet->title}}
              </th>
              <td class="border-t-0 px-6 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 ">  
                {{$diet->fecha}}
              </td>
              <td class="p-4 flex flex-row justify-end">
                <div class="p-3">
                  <a wire:click="openModal({{$diet}})" class="cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="30px" height="30px" viewBox="0 0 576 512"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M288 32c-80.8 0-145.5 36.8-192.6 80.6C48.6 156 17.3 208 2.5 243.7c-3.3 7.9-3.3 16.7 0 24.6C17.3 304 48.6 356 95.4 399.4C142.5 443.2 207.2 480 288 480s145.5-36.8 192.6-80.6c46.8-43.5 78.1-95.4 93-131.1c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C433.5 68.8 368.8 32 288 32zM144 256a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm144-64c0 35.3-28.7 64-64 64c-7.1 0-13.9-1.2-20.3-3.3c-5.5-1.8-11.9 1.6-11.7 7.4c.3 6.9 1.3 13.8 3.2 20.7c13.7 51.2 66.4 81.6 117.6 67.9s81.6-66.4 67.9-117.6c-11.1-41.5-47.8-69.4-88.6-71.1c-5.8-.2-9.2 6.1-7.4 11.7c2.1 6.4 3.3 13.2 3.3 20.3z"/></svg>
                  </a>
                </div>
                <div class="p-3">
                  <a wire:click="" class="cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="30px" height="30px" viewBox="0 0 448 512"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M135.2 17.7L128 32 32 32C14.3 32 0 46.3 0 64S14.3 96 32 96l384 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-96 0-7.2-14.3C307.4 6.8 296.3 0 284.2 0L163.8 0c-12.1 0-23.2 6.8-28.6 17.7zM416 128L32 128 53.2 467c1.6 25.3 22.6 45 47.9 45l245.8 0c25.3 0 46.3-19.7 47.9-45L416 128z"/></svg>
                  </a>
                </div>
                <div class="p-3">
                  <a wire:click="openModal({{$diet, true}})" class="cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="30px" height="30px" viewBox="0 0 512 512"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M471.6 21.7c-21.9-21.9-57.3-21.9-79.2 0L362.3 51.7l97.9 97.9 30.1-30.1c21.9-21.9 21.9-57.3 0-79.2L471.6 21.7zm-299.2 220c-6.1 6.1-10.8 13.6-13.5 21.9l-29.6 88.8c-2.9 8.6-.6 18.1 5.8 24.6s15.9 8.7 24.6 5.8l88.8-29.6c8.2-2.7 15.7-7.4 21.9-13.5L437.7 172.3 339.7 74.3 172.4 241.7zM96 64C43 64 0 107 0 160L0 416c0 53 43 96 96 96l256 0c53 0 96-43 96-96l0-96c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 96c0 17.7-14.3 32-32 32L96 448c-17.7 0-32-14.3-32-32l0-256c0-17.7 14.3-32 32-32l96 0c17.7 0 32-14.3 32-32s-14.3-32-32-32L96 64z"/></svg>
                  </a>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>

      </table>
    </div>
  </div>
</div>
</section>
```

Aquí, basicamente nos cogemos una *tabla pre-hecha* de **tailwindcomponents** y a esta le creamos un `@foreach` para que **nos muestre cada una de las dietas de ese usuario** y terminamos el bucle con un `@endforeach`. **IMPORTANTE** que todo el componente debe estar entre un `<section></section>`.

**Cabe resaltar** que al final de la tabla, metimos una tabla que usa ***svg's*** como iconos para que luego podamos darle una funcionalidad al ser clicados y que muestren los futuros **Modales**.

Por último, para que esa tabla se muestre en nuestro *Dashboard*, debemos ir a `resources/views/dashboard.blade.php` y añadimos nuestro componente:

```
<x-app-layout>
    <link rel="stylesheet" href="https://demos.creative-tim.com/notus-js/assets/styles/tailwind.css">
    <link rel="stylesheet" href="https://demos.creative-tim.com/notus-js/assets/vendor/@fortawesome/fontawesome-free/css/all.min.css">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ Auth::user()->name }} Dashboard
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ Auth::user()->name }}, estás dentro
                </div>
            </div>
        </div>
        <livewire:diets-table/>
    </div>
</x-app-layout>
```

Yo lo he añadido después de lo que te genera automáticamente el framework, usando `<livewire:diets-table/>` para meter el componente.

**También resaltar** que he modificado la lógica de los mensajes dejándolos como `{{ Auth::user()->name }} Dashboard` y `{{ Auth::user()->name }}, estás dentro`, que hace que *muestre el nombre del usuario junto al texto*, quedando algo más estético y personalizado, es una mera curiosidad.

## 6. EL MODAL Y LOS BOTONES
Ahora vamos a empezar con lo más denso del ejercicio, la programación más real, que puede asustar un poco, pero realmente no es nada que no sepamos ya.

### 6.1. LÓGICA DEL MODAL
Bien, sabemos que tenemos una tabla, con **3** botones en cada columna que hacen funciones de *lectura, eliminación y actualización* sobre esos elementos en la **BBDD**, a parte, tenemos **otro botón más** arriba de la tabla, que hace la función de *escritura* de elementos, bien, sobre esta lógica, nosotros queremos en **3 de los 4** botones que se **nos abra una ventana modal** que nos permita, o bien, *insertar, actualizar o ver* los datos de una dieta.

Para esto, es necesario que le demos una vuelta al archivo `app/Livewire/DietsTable.php`, consta resaltar que a partir de ahora estaremos saltando entre varios archivos, así que no siempre pondré todo el archivo completo de nuevo aquí en el MarkDown.

```
class DietsTable extends Component
{
    public $diets = [];
    public $myDiet;
    public $title;
    public $fecha;
    public $description;
    public $totalCalories;
    public $modal = false;
    public $isEditing = false;

    public function mount() {   
        $this->diets = Diet::where('user_id', Auth::id())->get();
    }
    public function render()
    {
        return view('livewire.diets-table');
    }

    private function clearFields(){
        $this->title = '';
        $this->fecha = '';
        $this->description = '';
        $this->totalCalories = '';
    }

    private function createDiet(){
        $this->clearFields();
        $this->modal = true;
    }

    public function openModal(Diet $diet = null, bool $isEditing = true){
        if ($diet){
            $this -> title = $diet -> title;
            $this -> fecha = $diet -> fecha;
            $this -> description = $diet -> description;
            $this -> totalCalories = $diet -> totalCalories;
            $this -> myDiet = $diet;
        } else {
            $this -> clearFields();
        }
        $this -> isEditing = $isEditing;
        $this->modal = true;
    }
}
```

Vale, aquí hacemos varias cosas que nos van a resultar interesantes:
* Adición de variables: hemos añadido muchas variables, entre ellas, varias contienen *los datos de la tabla diet*, como `$title, $fecha, $description o $totalCalories`, que nos servirán para *enlazar los datos que queremos mostrar en el componente con los datos que existen en el modelo*.
* `$myDiet`: es una variable que nos servirá como contenedor a la hora de, en la vista, *seleccionar una dieta específica* y que esta *le pase a la lógica la dieta en específico*.
* `$modal`: es una variable **booleana**, busca hacer que cuando sea **true** el modal se muestre por pantalla, mientras que siendo **false** se oculte.
* `$isEditing`: otro **booleano**, busca hacer que si es **true** los campos que muestre el modal sean editables o si es **false**, que sean texto plano.
* `private function clearFields(){`: Añadimos las variables que queremos que sean limpiadas cuando el modal deba estar vacío.
* `public function openModal(Diet $diet = null, bool $isEditing = true){`: Esta función es más compleja, lo primero, es que recibe dos cosas, un objeto **Diet**, que inicializa a null por defecto, y un booleano **$isEditing**, que nos permitirá saber si los campos llegan a ser o no editables. La función lo primero que hace es comprobar `if ($diet){`, es decir, **si $diet no es null**, en el caso de no serlo, hace que todas las **variables sean igualadas a los datos de esa dieta**, destacando `$this -> myDiet = $diet;`, que nos sirve para especificar que esta es la **dieta seleccionada**. Luego, tenemos el `} else {`, **si $diet es null** simplemente se llama a la función `$this -> clearFields();` para que los campos del modal estén vacíos. Terminando el **if/else**, hacemos dos cosas `$this -> isEditing = $isEditing;`, que **iguala nuestra variable a lo pasado por la función**, y por último, se hace que **el modal sea visible** con `$this->modal = true;`.

### 6.2. EL MODAL EN LA VISTA
Ahora vamos a volver a `resources/views/livewire/diets-table.blade.php` y vamos a añadir la lógica del modal justo antes de `</section>`:

```
@if ($modal){
<div class="fixed left-0 top-0 flex h-full w-full items-center justify-center bg-black bg-opacity-50 py-10">
  <div class="max-h-full w-full max-w-xl overflow-y-auto sm:rounded-2xl bg-white">
    <div class="w-full">
      <div class="m-8 my-20 max-w-[400px] mx-auto">
        <div class="mb-8">
          <h1 class="mb-4 text-3xl font-extrabold">Calorías de la dieta: {{$myDiet->totalCalories}}</h1>
          
          <label for="name">Nombre de la dieta: </label>
          @if ($isEditing)
            <input type="text" id="name" name="title" wire:model="title" value="{{ isset($myDiet) ? $myDiet->title : '' }}">
          @else
            <p>{{$myDiet->title}}</p>
          @endif

          <br>

          <label for="description">Descripción de la dieta: </label>
          @if ($isEditing)
            <input type="text" id="description" name="description" wire:model="description" value="{{ isset($myDiet) ? $myDiet->description : '' }}">
          @else
            <p>{{$myDiet->description}}</p>
          @endif

          <br>

          <label for="name">Fecha de la dieta: </label>
          <p>{{$myDiet->fecha}}</p>

          <br>

          <label for="totalCalories">Calorías de la dieta de la dieta: </label>
          @if ($isEditing)
            <input type="text" id="totalCalories" name="totalCalories" wire:model="totalCalories" value="{{ isset($myDiet) ? $myDiet->totalCalories : '' }}">
          @else
            <p>{{$myDiet->totalCalories}}</p>
          @endif

```

Vamos a ver qué hacemos aquí:
* `@if ($modal)`: comprueba si la variable booleana en el componente es **true** para ejecutar su código.
* **Modal**: cogido de ***livewirecomponents***, cualquiera nos vale, pero escogí uno que ya trajese botones incorporados para facilitar nuestro trabajo más adelante.
* `<label>` e `<input>`, que por ahora, tenemos la lógica a la mitad en este código, pero analizamos que lo que hace la aplicación es poner un `<label>` que indicará el nombre del campo a informar, en el primer caso, ***Nombre de la dieta***, y justo al lado, un `<input>` al que le pasamos dos cosas importantes; `wire:model="title"`, que referencia al campo al que corresponde, y `value="{{ isset($myDiet) ? $myDiet->title : '' }}"`, que le dará el valor al campo en el caso de ser una consulta de **edición**.
* `@endif`: termina el if.
* **DESTACAR** que `name=""` debe llamarse igual que el campo al que refiere.

Bien, como has podido notar, esto está incompleto, pero de momento, podemos dejarlo así, esto nos servirá para ver si nuestro modal coge algún dato ya de, si por ejemplo, *en la tabla seleccionamos la Dieta 1*, que nos muestre en el modal el nombre de esa dieta. Sin embargo, no tenemos definido **que los botones nos lleven a hacer aparecer el modal**.

### 6.3. LOS BOTONES MUESTRAN EL MODAL
En el componente **HTML**, definimos previamente algo muy importante, y es que los **3 iconos svg** y el **botón superior** de la tabla cuando son presionados, llaman al método `openModal`, para esto, necesitamos varios pasos:
* **En el caso del botón** debemos añadirle en las propiedades de la etiqueta un `wire:click="openModal"`, que significa que cuando es clickado, *livewire llama al componente* y ejecuta el método `openModal` pasándole ningún atributo, para que use los que tiene el método definidos por defecto.
* **En el caso de los svg** debemos meterlos dentro de un `<a></a>`, que usará el mismo `wire:click="openModal({{$diet, true}})"`, sólo que aquí, **las propiedades cambiarán** dependiendo del botón seleccionado, por ejemplo, el de ***ver la dieta*** pasará un `({{$diet}}, false)`, ya que *queremos ver una dieta específica pero no editar sus campos*, con el de ***borrar una dieta*** llamaremos a *otro método aún no creado* y con el de ***editar la dieta*** pasaremos `({{$diet}}, true)`, ya que *queremos ver los valores de la dieta y poder cambiarlos*.
* `@if (isEditing)` usamos esto para definir cuándo el campo es editable o no, si no lo es, mostrará el texto en un `<p></p>`, pero al serlo, será un `<input></input>`.

### 6.4. BOTÓN DE CERRAR EL MODAL
Vale, ahora el modal, dijimos que *habíamos seleccionado uno con dos botones para usarlos*, pues empecemos a programar la funcionalidad del segundo botón, que es sencilla, aunque primero **cambiamos el nombre al botón**; `<button wire:click="closeModal" class="p-3 bg-white border rounded-full w-full font-semibold">Cerrar</button>`, al cuál le hemos añadido la funcionalidad del click, pero debemos ahora **programar el método closeModal** en `app/Livewire/DietsTable.php`:

```
    public function closeModal(){
        $this->modal = false;
    }
```

Tan simple como **cambiar el modal a false**.

### 6.5. BOTÓN DE GUARDAR/ACTUALIZAR UNA DIETA
Bien, ahora debemos empezar con la lógica del primer botón, que o bien guardará la dieta y la insertará, o bien la actualizará, bien, lo primero es cambiar el **componente HTML**:

```
        <div class="space-y-4">
          @if ($isEditing)
            <button wire:click="updateCreateDiet" class="p-3 bg-black rounded-full text-white w-full font-semibold">
              {{isset($myDiet->id) ? 'Actualizar dieta' : 'Crear dieta'}}
            </button>
          @endif
          <button wire:click="closeModal" class="p-3 bg-white border rounded-full w-full font-semibold">Cerrar</button>
        </div>
```

Bien, con esto hacemos que ***únicamente*** cuando los campos son editables se permita ver un **botón que permite guardar los cambios**, ya que si sólo estamos *viendo* no hay cambios que puedan ser guardados.

A parte, estamos configurando que **el nombre del botón varie** dependiendo de si **la dieta es nula o no**, al no serlo, mostrará *actualizar dieta*, al ser nulo, *Crear dieta*.
Pero, este botón llama a una nueva función, que o bien la crea o actualiza, pero que no hemos creado, por lo que nos vamos a ello:

```
    public function updateCreateDiet(){
        if ($this -> myDiet->id){
            $diet = Diet::find($this -> myDiet->id);
            $diet -> update([
                'title' => $this -> title,
                'fecha' => now(),
                'description' => $this -> description,
                'totalCalories' => $this -> totalCalories
            ]);
        } else {
            $newDiet = new Diet();
            $newDiet -> title = $this -> title;
            $newDiet -> fecha = now();
            $newDiet -> description = $this -> description;
            $newDiet -> totalCalories = $this -> totalCalories;
            $newDiet -> user_id = Auth::id();
            $newDiet -> save();
        }

        $this -> clearFields();
        $this -> modal = false;
        $this -> diets = $this -> getDiets();
    }


    public function getDiets(){
        return Diet::all();
    }
```

Tenemos dos funciones, la primera `public function updateCreateDiet(){` usa un `@if ()` que comprueba que la dieta tenga un **id** pasa saber *si es nueva o se está actualizando*, y hacer así una función u otra.

Por otro lado, la función al final llama a otra llamada `getDiets()`, que definimos debajo, y simplemente actualiza la tabla principal para que **no tengamos que recargar la web de forma manual**.

### 6.6. BORRAR DIETAS
Bien, esto es relativamente sencillo, simplemente hacemos la siguiente función:

```
    public function delete(Diet $diet){
        $diet -> delete();
        $this -> diets = $this -> getDiets();
    }
```

Y la llamamos en el botón borrar:

```
<a wire:click="delete({{$diet}})" class="cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="30px" height="30px" viewBox="0 0 448 512"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M135.2 17.7L128 32 32 32C14.3 32 0 46.3 0 64S14.3 96 32 96l384 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-96 0-7.2-14.3C307.4 6.8 296.3 0 284.2 0L163.8 0c-12.1 0-23.2 6.8-28.6 17.7zM416 128L32 128 53.2 467c1.6 25.3 22.6 45 47.9 45l245.8 0c25.3 0 46.3-19.7 47.9-45L416 128z"/></svg>
                  </a>
```

***PROYECTO TERMINADO***
