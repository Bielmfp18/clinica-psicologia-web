<style>
    html,
    body {
        height: 100%;
        /*Garante que o rodapé ocupe toda a altura da tela*/
        display: flex;
        flex-direction: column;
    }

    main {
        flex-grow: -1;
        /*Faz o conteúdodo rodapé crescer e ficar embaixo da página*/
    }

    footer {
        background-color: black;
        padding: 10px 0;
        width: 100%;
    }

    .hidden {
        display: none !important;
    }
  .bi:hover {
    text-decoration: none !important;
    color: white !important;
    transition: transform 0.4s ease-in-out, color 0.7s ease-in-out;
    color: #DBA632 !important;
    transform: scale(1.1);
  }
</style>

<html>

<body>

    <main>
        <!-- Fundo do rodapé-->
        <footer class="bg-white text-dark pt-5 pb-3 mt-5">
            <div class="container">
                <div class="row">
                    <!-- Coluna da logo e redes sociais -->
                    <div class="col-12 d-flex justify-content-center">
                    <img src="image/MENTE_RENOVADA-LOGO.png" alt="Imagem-rodape" height="150px">
                        <!-- <h2 class="mb-3"></h2> -->
                        <!-- <hr class="hidden"> -->
                        <!-- <br><br> -->
                    </div>
                    <!-- <div class="col-md-2 col-md-2"></div> -->
                </div>

                <div class="row">
                    <div class="col-12 d-flex justify-content-center mb-3">
                        <a href="https://github.com/Bielmfp18/clinica-psicologia-web" class="text-dark me-3" style="text-decoration: none;"><i class="bi bi-github"> Desenvolvido por Gabriel Martins </i></a>
                    </div>
                </div>

                <div class="row">
                    <!-- Coluna de Informações adicionais -->
                    <div class="col-12 my-3 d-flex justify-content-center">
                        <p class="small text-center">
                            Endereço: R. Gregório Ramalho, 263, 2º andar, Itaquera, São Paulo - SP, CEP 08210-430<br>
                            MENTE RENOVADA CLÍICA DE PSICOLOGIA S.A - CNPJ: 01.234.567/0001-78
                        </p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 d-flex justify-content-center">
                        <p>&copy; 2025 Mente Renovada. Todos os direitos reservados.</p>
                    </div>
                </div>
            </div>
    </main>
    </footer>
</body>

</html>