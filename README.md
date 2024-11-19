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

Bien, ahora, antes de mostrar nada por pantalla, vamos a crear ***los controladores***, 
