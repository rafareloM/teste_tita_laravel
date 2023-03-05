# Desafio TiTa Therapy
O desafio consiste em receber um JSON através de um POST e retornar outro JSON com os dados tratados. O objetivo é fornecer os dados para montar um gráfico pizza. O JSON recebido contém um nome e um valor, como o do exemplo:

    {"Hausa": 4, "Yoruba" : 5, "Igbo" : 6, "Efik" : 1, "Edo" : 4}

Após receber os números, deve-se fornecer o valor equivalente ao ângulo do item no gráfico, dessa forma:

    {"Hausa":72,"Yoruba":90,"Igbo":108,"Efik":18,"Edo":72}


## Instação e configuração
Além de devolver os dados tratados, decidi também gravar os dados recebidos num banco de dados mySQL, portanto são necessárias algumas configurações no ambiente.

### Configurando o ambiente
Serão necessárias algumas ferramentas. No meu caso, usei o Docker para rodar o MySQL junto com o Postman para simular uma requisição. 

#### Criando um banco de dados mySQL: 
Será necessário ter o docker instalado. Caso não tenha, pode seguir a documentação: https://docs.docker.com/get-docker/

#### 1. Crie um contêiner MySQL, usando o seguinte comando:
      
    docker run -p 3306:3306 --name <nome-do-container> -e MYSQL_ROOT_PASSWORD=<senha-do-root> -d mysql

Esse código cria um container na porta 3306 definida com a flag `-p 3306:3306`, define o nome através da flag `--name <nome-do-container>`, define a senha do usuário root com a flag `-e MYSQL_ROOT_PASSWORD=<senha-do-root>`, roda como um daemon com a flag `-d` e seleciona a imagem do mysql.

#### 2. Acesse o container MySQL criado:

    docker exec -it <nome-do-container> mysql -p

Com esse comando o MySQL estará disponível no prompt ou terminal usado. É necessário lembrar que ele solicitará a senha após o comando. As flags `-it` e `-p` são necessárias, pois a primeira permite que o shell do MySQL seja acessado e a segunda serve para o MySQL ser notificado que a conexão é via terminal.

#### 3. Criando banco de dados: 

    CREATE DATABASE <nome_do_banco>;

Comando para criar banco de dados na sintaxe do SQL.

### Configurando o projeto

#### 1. Clone o projeto ou faça download do zip a partir do repositório.

#### 2. Instale as dependências do projeto usando o Composer.

    composer install

Para esse passo é necessário o gerenciador de pacotes Composer. Caso não tenha, pode seguir a documentação:
https://getcomposer.org/doc/00-intro.md#installation-windows

#### 3. Configurar o arquivo .env:
Copie o arquivo `.env.example` para `.env`. Modifique as variáveis do *DB* para as setadas no seu ambiente, como o nome do banco a porta utilizada e a senha do usuário root.


#### 4. Gerar as migrações: 

    php artisan migrate

Esse código irá executar as migrações do banco de dados e criar as tabelas necessárias.

#### 5. Rodar a aplicação localmente:

    php artisan serve
    
Por default, a porta escolhida será a 8000, mas usando a flag `--port` você pode definir a porta.


## Como usar a API
A API tem apenas uma rota configurada: `POST /angles`

#### Gerando os ângulos
Para gerar o ângulo, a requisição enviada deve ser do tipo **POST** e deve ser enviada para o url `localhost:<porta-do-projeto>/api/angles`. O Objeto JSON deve ser passado no **body** da requisição e seguir o seguinte formato:
  
    {seção1 : 2, seção2: 3, seção3: 2}

Onde cada chave vai corresponder ao nome da seção no gráfico e o valor a quantia dessa seção. O objeto passado pelo POST será salvo no banco de dados e a resposta da API será um objeto JSON com o nome passado e o valor correspondente ao ângulo no gráfico.
 
#### Exemplo de requisição:
Usando o Postman:
  
  ![image](https://user-images.githubusercontent.com/86268949/222932746-9c3c12b0-7ea0-43b8-816a-4f942aed814f.png)

#### Exemplo de resposta:
![image](https://user-images.githubusercontent.com/86268949/222932772-0219e497-d738-46ec-b8fa-c82a59f23651.png)

 ## Considerações:
 Decidi usar o Laravel para fazer a API, portanto meu primeiro passo foi rodar o comando:
    
    $ composer create-project --prefer-dist laravel/laravel teste_tita
  
  Logo após, precisei criar uma função PHP responsável por receber o JSON e tratar os dados recebidos, fiz isso da seguinte maneira:
  
  ~~~~PHP
  function findAngles(array $map): array{
  $angles= [];
  $total = array_sum($map);
  foreach($map as  $key => $value){
    $angle = round(($value / $total) * 360, 2);
    $angles[$key] = $angle;
  }
  return $angles;
}
  ~~~~
  
  - Nessa função, recebo e retorno um array, o primeiro passo da função é criar um array que será retornado após receber todos os dados do array `$map`, parâmetro da função.
  - Em seguida eu usei um método para somar todos os valores desse array e guardei dentro da variável `$total`, fiz isso para poder calcular proporcionalmente o ângulo de cada seção.
  - Utilizando o laço foreach no array `$map` e usando o operador `=>` para associar o índice, que no caso seria o nome fornecido no JSON com o respectivo valor, guardei a chave na variável `$key` e o valor na `$value`. Dentro do laço vou declarar uma variável `$angle` para guardar o valor do cálculo do ângulo, perceba que usei o método `round()` a fim de fixar em 2 as casas decimais. 
  - Depois adicionei o `$angle` com a indexando com a `$key` no array `$angles`.
  
  
  A fim de guardar a seção do gráfico no banco de dados criei um Model. Meu primeiro passo foi rodar o comando:
  
      php artisan make:model Sections
  
  O arquivo foi gerado em `app/Models`. Abrindo o arquivo, adicionei o seguinte trecho de código:
  
  ~~~~PHP
  protected $fillable = ['name', 'value'];
  ~~~~
  
  Isso é necessário para indicar que campos podem ser criados usando o método `Section::create()`.
  
  Em seguida, precisei configurar a rota POST. Fiz isso entrando na pasta routes e dentro do arquivo `api.php` eu fiz uma função para a rota `'/angles'`, da seguinte maneira:
  
  ~~~~PHP
  Route::post('/angles', function (Request $request){
  $json = $request->getContent();
  $data = json_decode($json, true);
  $items = [];
  foreach($data as $key => $value){
    $items[$key] = $value;
  }
  foreach($items as $key => $value){
    $section = Section::create([
      'name'=> $key, 'value' => $value
    ]);
  }
  $response = findAngles($items);
  return response()->json($response, 200);
});
  ~~~~
  
  - Nessa função eu recebo um parâmetro `$request`. Primeiramente pego o conteúdo usando o método `getContent()` dentro da varíavel `$request` e atribuindo a `$json`
  - Meu próximo passo foi guardar o decode do JSON numa variável `$data` usando o método `json_decode()`, após passar o objeto a ser decodificado passei a flag `true` para avisar o método que os valores são associados. 
  - Inicio a variável  como um array vazio a fim de guardar as informações de `$data`. 
  - Inicio um loop foreach em `$data` para alimentar a `$items` indexando chave e valor, como feito na função `findAngles()`
  - Em seguida crio outro foreach, dessa vez em `$items` para guardar os dados do JSON recebido no banco MySQL, faço isso usando o Section::create(), guardando os itens individualmente. No parâmetro name atribuo a chave do objeto, no value atribuo o valor.
  - Por fim, inicio uma variável `$response` e atribuo seu valor como o retorno da função `findAngles()`, passando `$items`.
  - No retorno na função, devolvo uma response em json com a variável `$response` e o status 200.
