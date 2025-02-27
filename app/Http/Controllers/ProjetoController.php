<?php

namespace App\Http\Controllers;

use App\Models\Projeto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProjetoController extends Controller
{
public function show()
{
    // Obtenha o aluno autenticado usando o guard 'alunos'
    $aluno = Auth::guard('alunos')->user();

    // Verifique se o aluno está autenticado
    if (!$aluno) {
        return redirect()->route('loginAluno')->withErrors('Você precisa estar logado para acessar essa página.');
    }

    // Busque o projeto associado ao aluno autenticado
    $projeto = Projeto::where('aluno_id', $aluno->id)->first();

    // Verifique se o projeto foi encontrado
    if (!$projeto) {
        return redirect()->route('alunoHome')->withErrors('Projeto não encontrado ou não autorizado.');
    }

    // Renderize a view 'Aluno/Projeto' com os dados do projeto
    return Inertia::render('Aluno/Projeto', [
        'projeto' => $projeto,
    ]);
}
    

    // Método para criar um novo projeto (renderiza o formulário)
    public function create()
    {
        return Inertia::render('/Aluno/CriarProjeto');
    }

    // Método para salvar o novo projeto
    public function store(Request $request)
{
    // Validação dos dados
    $validatedData = $request->validate([
        'titulo' => 'required|string|max:255',
        'descricao' => 'required|string',
        'dataInicio' => 'required|date',
        'dataFim' => 'required|date|after_or_equal:dataInicio   ',

    ]);
    
    // Temporariamente definindo um aluno_id padrão
    $aluno_id = '1'; // Ajuste este valor conforme necessário

    //\DB::table('projetos')->insert(array_merge($validatedData, ['aluno_id' => $aluno_id]));    
    // Criação do novo projeto
    $projeto = Projeto::create([
        'titulo' => $request->titulo,
        'descricao' => $request->descricao,
        'dataInicio' => $request->dataInicio,
        'dataFim' => $request->dataFim,
        'aluno_id' => $aluno_id, // Valor temporário até que o login esteja implementado
    ]);
    
    // Redireciona o aluno para a página inicial do aluno com uma mensagem de sucesso
    return redirect()->route('projeto.show', $projeto->id)->with('success', 'Projeto criado com sucesso!');
}

    public function update(Request $request)
{
    // Validação dos campos que podem ser enviados
    $validatedData = $request->validate([
        'titulo' => 'nullable|string|max:255', // Validação para o título
        'descricao' => 'nullable|string', // Validação para a descrição
        'objetivos' => 'array',
        'tecnologias' => 'array',
        'cronograma' => 'array',
        'informacoes_avulsas' => 'array',
    ]);

    // Obtenha o aluno autenticado
    $aluno = Auth::guard('alunos')->user();

    // Busque o projeto pelo aluno autenticado
    $projeto = Projeto::where('aluno_id', $aluno->id)->firstOrFail();

    // Atualiza os campos com os dados validados
    $projeto->titulo = $validatedData['titulo'] ?? $projeto->titulo;
    $projeto->descricao = $validatedData['descricao'] ?? $projeto->descricao;
    $projeto->objetivos = $validatedData['objetivos'] ?? $projeto->objetivos;
    $projeto->tecnologias = $validatedData['tecnologias'] ?? $projeto->tecnologias;
    $projeto->cronograma = $validatedData['cronograma'] ?? $projeto->cronograma;
    $projeto->informacoes_avulsas = $validatedData['informacoes_avulsas'] ?? $projeto->informacoes_avulsas;

    $projeto->save();

    // Retorna uma resposta de sucesso em JSON
    return response()->json(['message' => 'Projeto atualizado com sucesso!'], 200);
}


public function index()
    {
        // Busca todos os projetos com os nomes dos alunos
        $projetos = Projeto::with('aluno')->get(); // Assumindo que há um relacionamento 'aluno'

        // Formatar a resposta para incluir o nome do aluno
        $formattedProjects = $projetos->map(function ($projeto) {
            return [
                'id' => $projeto->id,
                'titulo' => $projeto->titulo,
                'nomeAluno' => $projeto->aluno->nome // Altere conforme o nome do campo
            ];
        });

        return response()->json($formattedProjects);
    }

}