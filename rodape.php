<footer class="site-footer" role="contentinfo" aria-label="Rodapé do site">
    <div class="container">
    
    <style>
        :root {
            --footer-bg: #ffffff;
            --accent: #DBA632;
            --text-muted: #585858;
        }
    
    
        footer.site-footer {
            background-color: #87698a;
            color: white;
            /* Corrigido para texto branco */
            padding: 3rem 0;
            width: 100%;
            border-top: 1px solid #e9e9e9;
        }
    
        .footer-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 2rem;
            flex-wrap: wrap;
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 1rem;
        }
    
        .footer-brand {
            display: flex;
            flex-direction: column;
            gap: .35rem;
            align-items: center;
            justify-content: flex-start;
        }
    
        .footer-brand img {
            height: 96px;
            width: auto;
        }
    
        .social-list {
            display: flex;
            gap: .6rem;
            margin-top: .2rem;
        }
    
        .social-list a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: white;
            color: #333;
            /* Cor dos ícones */
            text-decoration: none;
            transition: transform .15s ease;
        }
    
        .social-list a:hover {
            transform: translateY(-3px);
            color: #DBA632;
        }
    
        .footer-address {
            text-align: center;
            color: white;
            font-size: .95rem;
        }
    
        .footer-address p {
            margin: 0;
        }
    
        .footer-nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            gap: 1.1rem;
        }
    
      .footer-nav a {
        position: relative;
        color: white;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.3s ease; /* Faz a transição suave da cor */
    }
    
    .footer-nav a::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: -4px;
        height: 2px;
        width: 0;
        background: #DBA632;
        transition: width 0.3s ease;
    }
    
    .footer-nav a:hover {
        color: #DBA632; /* Muda a cor do texto no hover */
    }
    
    .footer-nav a:hover::after {
        width: 100%;
    }
    
    
    
        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            /* Linha mais sutil */
            margin-top: 1.75rem;
            padding-top: 1rem;
            color: white;
            text-align: center;
            font-size: .9rem;
        }
    
        /* Small screen behaviour */
        @media (max-width: 1100px) {
            .footer-inner {
                flex-direction: column;
                align-items: center;
                text-align: center;
                gap: 2rem;
            }
    
            .footer-brand,
            .footer-address,
            .footer-nav {
                margin-bottom: 1.5rem;
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
    
            /* Anula margin-left do desktop apenas no responsivo */
            .footer-nav {
                order: 3;
                margin-left: 0;
                padding: 0 1rem;
                justify-content: center;
                height: auto;
            }
    
            /* Transforma os links em coluna e centraliza */
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
    </style>

        <div class="footer-inner">
            <div class="footer-brand" aria-label="Marca e redes sociais">
                <img src="image/MENTE_RENOVADA-LOGO-removebg-preview-removebg-preview.png" alt="Logotipo Mente Renovada">
                <nav class="social-list" aria-label="Redes sociais">
                    <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="mailto:mente.renovada@gmail.com" aria-label="E-mail"><i class="bi bi-envelope"></i></a>
                </nav>
            </div>

            <div class="footer-address">
                <p>
                    Endereço: R. Gregório Ramalho, 263, 2º andar<br>
                    Itaquera, São Paulo - SP, CEP 08210-430<br>
                    MENTE RENOVADA CLÍNICA DE PSICOLOGIA S.A<br>
                    CNPJ: 01.234.567/0001-78
                </p>
            </div>

            <div class="footer-nav" aria-label="Navegação do rodapé">
                <ul>
                    <li><a href="#">Sobre</a></li>
                    <li><a href="#">Contato</a></li>
                    <li><a href="#">Suporte</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            &copy; 2025 Mente Renovada. Todos os direitos reservados.
        </div>
    </div>
</footer>