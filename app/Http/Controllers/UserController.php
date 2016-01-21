<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;
use Validator;
use Crypt;
use Hash;

class UserController extends Controller {

    private $request;
    private $user;

    public function __construct(Request $request, User $user) {
        $this->request = $request;
        $this->user = $user;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getIndex() {
        $titulo = 'Usuários | Curso de Laravel 5';
        $users = User::paginate(5);
        $status = '';
        if($this->request->session()->has('status')):
            $status = $this->request->session()->get('status');
        endif;
        return view('painel.users.index', compact('users', 'titulo', 'status'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function getAdicionar() {
        return view('painel.users.create-edit');
    }

    public function postAdicionar() {
        $dadosForm = $this->request->all();

        $validator = Validator::make($dadosForm, User::$rules);
        if ($validator->fails()) {
            return redirect('users/adicionar')
            ->withErrors($validator)
            ->withInput();
        }

        $dadosForm['password'] = Hash::make($dadosForm['password']);

        $status = 'Usuario '. $dadosForm['name']. ' cadastrado com sucesso!';
        $this->user->create($dadosForm)->save();
        $this->request->session()->flash('status', $status);
        return redirect('users');
    }

    public function getDeletar($idUser) {
        $this->user->find($idUser)->delete();

        return redirect('users');
    }

    public function getEditar($id) {
        $user = $this->user->find($id);

        $titulo = "Editar {$user->name} | Gestão do Usuário";

        return view('painel.users.create-edit', ['user' => $user, 'titulo' => $titulo]);
    }

    public function postEditar($id) {
        $dadosForm = $this->request->all();

        $rules = [
        'name' => 'required|min:3|max:150',
        'email' => "required|email|max:250|unique:users,email,$id",
        'password' => 'required|min:3|max:20',
        ];
        $validador = Validator::make($dadosForm, $rules);
        if( $validador->fails() ){
            return redirect("users/editar/$id")
            ->withErrors($validador)
            ->withInput();
        }
        $dadosForm = $this->request->except('_token');
        $dadosForm['password'] = Hash::make($dadosForm['password']);
        $this->user->where('id',$id)->update($dadosForm);
        $status = 'Usuario '. $dadosForm['name']. ' editado com sucesso!';
        $this->request->session()->flash('status', $status);

        return redirect('users');
    }

    public function getVerificaHash($id,$senha)
    {
        $user       = $this->user->find($id);
        $password   = $this->user->password;

        if (Hash::check($senha,  $password))
        {
            echo "OK! Hash validado";
        } else {
            echo "Ops! Confira seus dados";
        }

    }




}
