<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SSOController extends Controller
{
    /**
     * Redirect to SSO authorization server.
     */
    public function redirect(Request $request)
    {
        $request->session()->put('state', $state = Str::random(40));
        $request->session()->put('code_verifier', $code_verifier = Str::random(128));

        $codeChallenge = str_replace('=', '', strtr(base64_encode(hash('sha256', $code_verifier, true)), '+/', '-_'));

        $query = http_build_query([
            'client_id' => config('services.sso.client_id'),
            'redirect_uri' => config('services.sso.redirect_uri'),
            'response_type' => 'code',
            'scope' => '',
            'state' => $state,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
        ]);

        Log::info('SSO Redirect', [
            'state' => $state,
            'session_id' => $request->session()->getId(),
        ]);

        $request->session()->save();

        return redirect(config('services.sso.base_url') . '/oauth/authorize?' . $query);
    }

    /**
     * Handle SSO callback and exchange code for access token.
     */
    public function callback(Request $request)
    {
        $state = $request->session()->get('state');
        $codeVerifier = $request->session()->get('code_verifier');

        Log::info('SSO Callback: Validando State', [
            'expected' => $state,
            'received' => $request->state,
        ]);

        if (!$state || $state !== $request->state) {
            Log::error('Erro SSO: State inválido ou expirado.');
            abort(403, 'Invalid state');
        }

        $request->session()->forget(['state', 'code_verifier']);

        // 1. TENTANDO OBTER O ACCESS TOKEN
        Log::info('SSO: Solicitando Access Token...', [
            'url' => config('services.sso.base_url') . '/oauth/token',
            'client_id' => config('services.sso.client_id'),
        ]);

        $response = Http::asForm()
            ->post(config('services.sso.base_url') . '/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => config('services.sso.client_id'),
            'client_secret' => config('services.sso.client_secret'),
            'redirect_uri' => config('services.sso.redirect_uri'),
            'code_verifier' => $codeVerifier,
            'code' => $request->code,
        ]);

        if ($response->failed()) {
            Log::error('Erro SSO: Falha na troca do Token', [
                'status' => $response->status(),
                'error' => $response->body()
            ]);
            abort(500, 'Erro ao trocar o código pelo token de acesso.');
        }

        $accessToken = $response->json('access_token');
        Log::info('SSO: Access Token obtido com sucesso.');

        // 2. TENTANDO BUSCAR DADOS DO USUÁRIO
        $userDataResponse = Http::withToken($accessToken)
            ->get(config('services.sso.base_url') . '/api/user');

        if ($userDataResponse->failed()) {
            Log::error('Erro SSO: Falha ao buscar dados do usuário', [
                'status' => $userDataResponse->status(),
                'response' => $userDataResponse->body()
            ]);
            abort(500, 'Erro ao buscar dados do usuário no servidor SSO.');
        }

        $userData = $userDataResponse->json();
        Log::info('SSO: Dados do usuário recebidos', ['email' => $userData['email'] ?? 'N/A']);

        // 3. TENTANDO PERSISTIR NO BANCO
        try {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'sso_id' => $userData['id'],
                    'password' => null,
                ]
            );

            Auth::login($user);
            Log::info('SSO: Usuário autenticado com sucesso.', ['email' => $user->email]);

            return redirect('/dashboard');

        } catch (\Exception $e) {
            Log::error('Erro SSO: Falha ao salvar usuário no banco de dados', [
                'message' => $e->getMessage(),
                'data' => $userData
            ]);
            abort(500, 'Erro interno ao processar os dados do usuário.');
        }
    }
}
