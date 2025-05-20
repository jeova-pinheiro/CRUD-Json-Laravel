<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class UsuarioController extends Controller
{
    private string $filePath = 'storage/app/usuarios.json';

    private function readData(): array
    {
        if (!file_exists(base_path($this->filePath))) {
            return [];
        }

        $data = file_get_contents(base_path($this->filePath));
        return json_decode($data, true) ?? [];
    }

    private function writeData(array $usuarios): void
    {
        file_put_contents(base_path($this->filePath), json_encode($usuarios, JSON_PRETTY_PRINT));
    }

    public function index(): JsonResponse
    {
        $usuarios = $this->readData();

        return response()->json([
            'status' => true,
            'usuarios' => $usuarios
        ], 200);
    }

    public function show(int $id): JsonResponse
    {
        $usuarios = $this->readData();

        $usuario = collect($usuarios)->firstWhere('id', $id);

        if (!$usuario) {
            return response()->json([
                'status' => false,
                'message' => 'Usuário não encontrado!'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'usuario' => $usuario
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $usuarios = $this->readData();

        $novoUsuario = [
            'id' => count($usuarios) > 0 ? max(array_column($usuarios, 'id')) + 1 : 1,
            'nome' => $request->input('nome'),
            'email' => $request->input('email'),
            'telefone' => $request->input('telefone'),
        ];

        $usuarios[] = $novoUsuario;
        $this->writeData($usuarios);

        return response()->json([
            'status' => true,
            'usuario' => $novoUsuario,
            'message' => 'Usuário cadastrado com sucesso!'
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $usuarios = $this->readData();

        $index = collect($usuarios)->search(fn($u) => $u['id'] == $id);

        if ($index === false) {
            return response()->json([
                'status' => false,
                'message' => 'Usuário não encontrado!'
            ], 404);
        }

        $usuarios[$index] = array_merge($usuarios[$index], $request->only(['nome', 'email', 'telefone']));
        $this->writeData($usuarios);

        return response()->json([
            'status' => true,
            'usuario' => $usuarios[$index],
            'message' => 'Usuário atualizado com sucesso!'
        ], 200);
    }

    public function destroy(int $id): JsonResponse
    {
        $usuarios = $this->readData();

        $index = collect($usuarios)->search(fn($u) => $u['id'] == $id);

        if ($index === false) {
            return response()->json([
                'status' => false,
                'message' => 'Usuário não encontrado!'
            ], 404);
        }

        $usuarioRemovido = $usuarios[$index];
        unset($usuarios[$index]);
        $this->writeData(array_values($usuarios));

        return response()->json([
            'status' => true,
            'usuario' => $usuarioRemovido,
            'message' => 'Usuário removido com sucesso!'
        ], 200);
    }
}
