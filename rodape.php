 <style>
      :root {
          --footer-bg: #ffffff;
          --card-bg: #f6f7f8;
          --accent: #DBA632;
          --text-muted: #585858;
          --text-main: #000000;
          --container-max: 1100px;
      }

      /* base */
      *,
      *::before,
      *::after {
          box-sizing: border-box;
      }

      html,
    body {
        height: 100%;
        /*Garante que o rodapé ocupe toda a altura da tela*/
        display: flex;
        flex-direction: column;
    }

    main {
        flex-grow: -1;
        /*Faz o conteúdo do rodapé crescer e ficar embaixo da página*/
    }

    footer {
        background-color: black;
        padding: 10px 0;
        width: 100%;
    }

    .hidden {
        display: none !important;
    }


      /* footer */
      footer.site-footer {
          background-color: var(--footer-bg);
          color: var(--text-main);
          padding: 3rem 0;
          width: 100%;
          border-top: 1px solid #e9e9e9;
          margin-top: 7rem;
      }

      .container {
          width: 100%;
          max-width: var(--container-max);
          margin: 0 auto;
          padding: 0 1rem;
      }

      /* grid: colunas previsíveis */
      .footer-inner {
          display: grid;
          grid-template-columns: minmax(160px, 220px) 1fr minmax(260px, 360px);
          gap: 1.25rem;
          align-items: center;
          /* centraliza verticalmente cada coluna */
          max-width: var(--container-max);
          margin: 0 auto;
          padding: 0 1rem;
      }

      /* marca (logo + ícones) */
      .footer-brand {
          display: flex;
          flex-direction: column;
          gap: .35rem;
          align-items: center;
          justify-content: flex-start;
      }

      .footer-brand img {
          height: 96px;
          /* equilibrado visualmente */
          width: auto;
          max-width: 100%;
          display: block;
          margin: 0;
      }

      .social-list {
          display: flex;
          gap: .6rem;
          align-items: center;
          justify-content: center;
          margin-top: .2rem;
      }

      .social-list a {
          display: inline-flex;
          align-items: center;
          justify-content: center;
          width: 40px;
          height: 40px;
          border-radius: 50%;
          background: rgba(0, 0, 0, 0.06);
          color: var(--text-main);
          text-decoration: none;
          font-size: 1.05rem;
          transition: transform .15s ease, background .15s ease, color .15s ease;
      }

      .social-list a:focus,
      .social-list a:hover {
          transform: translateY(-3px);
          background: rgba(219, 166, 50, 0.12);
          color: var(--accent);
          outline: none;
      }

      /* nav central — ocupa a altura da célula e centraliza conteudo */
      .footer-nav {
          display: flex;
          align-items: center;
          justify-content: center;
          height: 100%;
          margin-left: 110px;
      }

      .footer-nav ul {
          list-style: none;
          padding: 0;
          margin: 0;
          display: flex;
          gap: 1.1rem;
          flex-wrap: wrap;
          justify-content: center;
      }

      .footer-nav a {
          color: var(--text-muted);
          text-decoration: none;
          font-weight: 500;
      }

      .footer-nav a:focus,
      .footer-nav a:hover {
          color: var(--text-main);
          text-decoration: underline;
      }

      /* newsletter (direita) */
      .footer-newsletter {
          display: flex;
          flex-direction: column;
          align-items: flex-end;
          /* à direita no desktop */
          justify-content: center;
          /* centraliza verticalmente */
          text-align: right;
      }

      .newsletter-title {
          font-weight: 600;
          margin-bottom: .45rem;
      }

      .newsletter-box {
          min-width: 230px;
          display: flex;
          gap: .5rem;
          align-items: center;
      }

      .newsletter-box input {
          padding: .6rem .75rem;
          border-radius: 4px;
          border: 1px solid #e6e6e6;
          background: #ffffff;
          color: var(--text-main);
          outline: none;
          flex: 1;
          min-width: 0;
          height: 40px;
      }

      .newsletter-box input::placeholder {
          color: #8a8a8a;
      }

      .newsletter-box button {
          padding: .55rem .85rem;
          border-radius: 6px;
          border: none;
          background: var(--text-main);
          color: #ffffff;
          font-weight: 600;
          cursor: pointer;
          height: 40px;
      }

      .newsletter-box button:focus {
          outline: 3px solid rgba(0, 0, 0, 0.08);
      }

      /* endereço + copyright */
      .footer-address {
          max-width: var(--container-max);
          margin: 1.8rem auto 0;
          padding: 0 1rem;
          text-align: center;
          color: var(--text-muted);
          font-size: .95rem;
      }

      .footer-address p {
          margin: 0;
      }

      .footer-bottom {
          border-top: 1px solid #f0f0f0;
          margin-top: 1.75rem;
          padding-top: 1rem;
          color: var(--text-muted);
          text-align: center;
          font-size: .9rem;
      }

      /* small screen behaviour */
      @media (max-width: 1100px) {
          .footer-inner {
              grid-template-columns: minmax(160px, 200px) 1fr;
              gap: 1rem;
          }

          .footer-newsletter {
              align-items: flex-start;
              text-align: left;
          }
      }

      /* === RESPONSIVO: telas médias (<= 900px) === */
      @media (max-width: 900px) {
        .footer-inner {
          grid-template-columns: 1fr;
          text-align: center;
          gap: 1rem;
        }

        .footer-brand {
          align-items: center;
        }

        /* anula margin-left do desktop apenas no responsivo */
        .footer-nav {
          order: 3;
          margin-left: 0;
          padding: 0 1rem;
          justify-content: center;
          height: auto;
        }

        /* transforma os links em coluna e centraliza */
        .footer-nav ul {
          flex-direction: column;
          gap: .5rem;
          align-items: center;
          margin: 0;
          padding: 0;
        }

        .footer-nav li {
          width: auto;
        }

        .footer-nav a {
          display: inline-block;
          padding: .25rem .35rem;
          font-size: 0.98rem;
        }

        .footer-newsletter {
          order: 2;
          justify-content: center;
          align-items: center;
          text-align: center;
        }
      }

      /* === RESPONSIVO: celulares pequenos (<= 576px) === */
      @media (max-width: 576px) {
        footer.site-footer {
          padding: 2rem 0;
        }

        .newsletter-box {
          flex-direction: column;
          gap: .5rem;
        }

        .newsletter-box button,
        .newsletter-box input {
          width: 100%;
        }

        .footer-inner {
          padding: 0 .75rem;
        }

        .footer-brand img {
          height: 84px;
        }

        .social-list a {
          width: 36px;
          height: 36px;
        }

        .footer-nav {
          margin-left: 0;
          padding: 0;
        }

        .footer-nav ul {
          gap: .6rem;
          width: 100%;
          flex-direction: column;
          align-items: center;
        }

        .footer-nav a {
          width: auto;
          font-size: 0.95rem;
          padding: .35rem .25rem;
        }

        .footer-address {
          font-size: .92rem;
          padding: 0 .6rem;
          line-height: 1.35;
        }
      }

      /* misc */
      .visually-hidden {
          position: absolute !important;
          height: 1px;
          width: 1px;
          overflow: hidden;
          clip: rect(1px, 1px, 1px, 1px);
          white-space: nowrap;
      }
  </style>
  </head>

  <body>
      <main>
    
          <footer class="site-footer" role="contentinfo" aria-label="Rodapé do site">
              <div class="container">
                  <div class="footer-inner">

                      <div class="footer-brand" aria-label="Marca e redes sociais">
                          <img src="image/MENTE_RENOVADA-LOGO-removebg-preview-removebg-preview.png" alt="Logotipo Mente Renovada">
                          <nav class="social-list" aria-label="Redes sociais">
                              <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                              <a href="#" aria-label="GitHub"><i class="bi bi-github"></i></a>
                              <a href="mailto:programeast0206@gmail.com" aria-label="E-mail"><i class="bi bi-envelope"></i></a>
                          </nav>
                      </div>

                      <div class="footer-nav" aria-label="Navegação do rodapé">
                          <ul>
                              <li><a href="#">Sobre</a></li>
                              <li><a href="#">Contato</a></li>
                              <li><a href="#">Suporte</a></li>
                          </ul>
                      </div>

                      <div class="footer-newsletter" aria-label="Assine nossa newsletter">
                          <div class="newsletter-title">Assine nossa newsletter</div>
                          <form class="newsletter-box" action="#" method="post" onsubmit="return false;">
                              <label for="newsletter-email" class="visually-hidden">E-mail</label>
                              <input id="newsletter-email" type="email" name="email" placeholder="Digite seu e-mail" required>
                              <button type="submit">Enviar</button>
                          </form>
                      </div>

                  </div>

                  <div class="footer-address">
                      <p>
                          Endereço: R. Gregório Ramalho, 263, 2º andar, Itaquera, São Paulo - SP, CEP 08210-430<br>
                          MENTE RENOVADA CLÍNICA DE PSICOLOGIA S.A - CNPJ: 01.234.567/0001-78
                      </p>
                  </div>

                  <div class="footer-bottom">
                      &copy; 2025 Mente Renovada. Todos os direitos reservados.
                  </div>
              </div>
          </footer>
      </main>
  </body>

  </html>