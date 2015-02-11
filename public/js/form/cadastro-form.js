/**
 * IEDCMCMV (http://mcmv.duquedecaxias.rj.gov.br)
 *
 * @link      https://github.com/irvingoliveira/iedcmcmv.git para o código-fonte
 * @copyright Copyright (c) 2013 Irving Fernando de Medeiros Oliveira, Guilherme Sousa dos Santos,
 * Eduardo Agusto Costa Bastos, Secretaria Municipal de Controle Interno, Ciência, Tecnologia e Sistemas de Duque de Caxias - RJ 
 * @license   http://www.gnu.org/licenses/gpl.txt GNU General Public Licence Version 3
 * 
 * Este arquivo é parte do programa IEDCMCMV
 *
 * IEDCMCMV é um software livre; você pode redistribuí-lo e/ou 
 * modificá-lo dentro dos termos da Licença Pública Geral GNU como 
 * publicada pela Fundação do Software Livre (FSF); na versão 3 da 
 * Licença.
 *
 * Este programa é distribuído na esperança de que possa ser  útil, 
 * mas SEM NENHUMA GARANTIA; sem uma garantia implícita de ADEQUAÇÃO a qualquer
 * MERCADO ou APLICAÇÃO EM PARTICULAR. Veja a
 * Licença Pública Geral GNU para maiores detalhes.
 * 
 * Você deve ter recebido uma cópia da Licença Pública Geral GNU
 * junto com este programa, se não, escreva para a Fundação do Software
 * Livre(FSF) Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
$(document).ready(function() {
    
    $("#termo").change(function(){
        if($(this).prop("checked"))
            $("#inscrevase").removeAttr("disabled","disabled");
        else
            $("#inscrevase").attr("disabled","disabled");
    });
    
    $("#err").hide();
    $("#help").hide();
    
    $('legend').each(function(){
        if(($(this).text() === "Conjuge") || ($(this).text() === "Identidade do conjuge"))
            $(this).parent().hide();
    });
    
    $('legend').each(function(){
        if($(this).text() === "Você é mulher chefe de família com dependentes?")
            $(this).parent().hide();
    });
    
    $('#cadastro-form').validate({
        debug: false,
        errorClass: "invalid-input",
        errorLabelContainer: "#limbo",
        wrapper: "li",
        errorPlacement: function(error,element) {
            return true;
        },
        invalidHandler: function(event, validator) {
            var errors = validator.numberOfInvalids();
            if (errors) {
                $("#err").fadeIn("fast")
                if(errors === 1)
                    $("#err").text("Existe "+ errors +" campo preenchido de forma inválida.");
                else
                    $("#err").text("Existem "+ errors +" campos preenchidos de forma inválida.");
            } else {
                $("#err").fadeOut("fast");
            }
        },        
    });

    /*
     * Validação Fieldset Titular
     */

    $('input[type=text]').blur(function() {
        $(this).val($(this).val().toUpperCase());
        $('#help').fadeOut("fast");
    });

    $("#titularNome")
        .focus(function (){
            $("#help").fadeIn("fast")
                      .text("Preencha o nome completo do solicitande do Minha Casa, minha vida.");
        })
       .rules("add", {
        
            required: true,
            minlength: 3,
            maxlength: 200,
        
            messages: {
                required: "O nome do Titular é obrigatório.",
                minlength: "O nome do Titular deve ter no mínimo três caracteres.",
                maxlength: "O nome do Titular deve ter no máximo duzentos caracteres."

            }
        });


    $("#titularCpf").mask('000.000.000-00')
        .focus(function (){
            $("#help").fadeIn("fast")
                      .text("Preencha o CPF do solicitande. (Somente números)");
        })
        .blur(function (){
            var cpf = $(this).val();
            $.post('/application/index/cpfunico',{cpf: cpf},function(data){
                if(data==="1"){
                    alert('Este cpf já foi cadastrado!');
                    $("#titularCpf").val("");
                }
               
            });
        })
        .rules("add", {
            required: true,
            cpf: "cpf",
            minlength: 14,
            maxlength: 14,
            messages: {
                required: "O CPF do Titular é obrigatório.",
                cpf: "O CPF do titular é inválido!",
                minlength: "O CPF do Titular não é valido.",
                maxlength: "O CPF do Titular não é valido."

            }
        });

    $("#titularDataNascimento")
        .focus(function (){
            $("#help").fadeIn("fast")
                      .text("Preencha a data de nascimento do solicitante. (Somente números)");
        })
        
        .blur(function (){
            $("#help").fadeOut("fast");
        })
        
        .mask('00/00/0000')
        
        .rules("add", {
            required: true,
            minlength: 10,
            maxlength: 10,
            dateNascBR: true,
            messages: {
                required: "A data de nascimento do Titular é obrigatório.",
                minlength: "A data de nascimento do Titular não é valida.",
                maxlength: "A data de nascimento do Titular não é valida.",
                dateNascBR: "Data de nascimento do titular inválida!",

            }
        });

    $('#titularEstadoCivil').change(function(){
        if(($(this).val() === "2") || ($(this).val() === "5")){
            $('legend').each(function(){
                if(($(this).text() === "Conjuge") || ($(this).text() === "Identidade do conjuge"))
                    $(this).parent().fadeIn("fast");
            });
        }else{
            $('legend').each(function(){
                if(($(this).text() === "Conjuge") || ($(this).text() === "Identidade do conjuge"))
                    $(this).parent().fadeOut("fast");
            });
        }
        $("#help").fadeOut("fast")
    })
    .focus(function (){
            $("#help").fadeIn("fast")
                .text("Selecione em que estado cívil o solicitante se encontra.");
    })
    .blur(function (){
        $("#help").fadeOut("fast");
    })
    .rules("add", {
        
            required: true,
           
            messages: {
                required: "O estado civil do Titular é obrigatório.",
            }
        });

    $("#titularNaturalidade")
        .focus(function (){
            $("#help").fadeIn("fast")
                .text("Selecione em que cidade o solicitante nasceu.");
        })
        .blur(function (){
            $("#help").fadeOut("fast");
        })
        .rules("add", {
        
            required: true,
           
            messages: {
                required: "A naturalidade do Titular é obrigatório.",
           
            }
        });

    $("#titularSexo")
        .focus(function (){
            $("#help").fadeIn("fast")
                      .text("Selecione o sexo do solicitante.");
        })
        .blur(function (){
            $("#help").fadeOut("fast");
        })
        .change(function(){
            if($(this).val() === "2"){
                $('legend').each(function(){
                    if($(this).text() === "Você é mulher chefe de família com dependentes?")
                        $(this).parent().fadeIn("fast");
                });
            }else{
                $('legend').each(function(){
                    if($(this).text() === "Você é mulher chefe de família com dependentes?")
                        $(this).parent().fadeOut("fast");
                });
            }
        })
        .rules("add", {
        
            required: true,
        
            messages: {
                required: "O sexo do Titular é obrigatório.",
            }
        });

    $(".titularMulherChefeDeFamilia")
        .mouseover(function (){
            var msg = "<p>Informe se o solicitante é mulher chefe de família</p>";
            msg+= "<p>Considera-se mulher chefe de família com dependentes, a mulher";
            msg+= " que é responsável pelo sustento de família e que possui crianças";
            msg+= " e adolescentes ou incapazes, que dependem diretamente dos rendimentos";
            msg+= " e cuidados da mesma.</p>";
            
            $("#help").fadeIn("fast")
                      .html(msg);
        })
        .mouseout(function (){
            $("#help").fadeOut("fast");
        })
        .rules("add", {
            required: true,
            messages: {
                required: "É necessário informar se o titular se encontra em abrigo institucional."
            }
        });

    $(".titularAcolhimentoInstitucional")
        .mouseover(function (){
            var msg = "<p>Informe se o solicitante se encontra em abrigo ";
            msg+= "institucional. </p>";
            msg+= "<p>Considera-se a situação de acolhimento institucional ";
            msg+= " àquelas famílias que possuem algum de seus membros acolhidos";
            msg+= " pela rede socioassistencial ou por instituições prisionais.</p>";
            
            $("#help").fadeIn("fast")
                      .html(msg);
        })
        .mouseout(function (){
            $("#help").fadeOut("fast");
        })
        .rules("add", {
            required: true,
            messages: {
                required: "É necessário informar se o titular se encontra em abrigo institucional."
            }
        });

    $(".titularBolsaFamilia")
        .mouseover(function (){
            $("#help").fadeIn("fast")
                      .text("Informe se o solicitante recebe Bolsa Família.");
        })
        .mouseout(function (){
            $("#help").fadeOut("fast");
        })
        .rules("add", {
            required: true,
            messages: {
                required: "É necessário informar se possui Bolsa Família"
            }
        });

    $(".titularImovel")
        .mouseover(function (){
            var msg = "<p>Informe se o solicitante tem algum tipo de imóvel como";
            msg+= " terreno, casa, apartamento, etc.</p>";
            msg+= " <p>Quem tem imóvel (terreno, casa, apartamento) em seu nome não";
            msg+= " tem direito ao Minha Casa Minha Vida. Os Agentes Financeiros";
            msg+= " fazem pesquisa para identificar os candidatos que pagam IPTU";
            msg+= " e tem imóveis em seu nome.</p>";
            
            $("#help").fadeIn("fast")
                      .html(msg);
        })
        .mouseout(function (){
            $("#help").fadeOut("fast");
        })
        .rules("add", {
            required: true,
            messages: {
                required: "É necessário informar se possui imóvel"
            }
        });

    $(".titularFinanciamentoCasa")
        .mouseover(function (){
            var msg = "<p>Informe se o solicitante tem ou já teve algum financiamento";
            msg+= " de casa/apartamento.</p>";
            msg+= "<p>Quem já teve imóvel financiado pelo antigo Sistema Financeiro";
            msg+= " de Habitação, Banco Nacional da Habitação - BNH, ou recebeu";
            msg+= " imóvel do sistema de COHAB, mesmo que em outro estado da Federação";
            msg+= " não será atendido, conforme os critério do Programa Minha Casa";
            msg+= " Minha Vida, ficando assim incompatível ao programa.</p>";
            
            $("#help").fadeIn("fast")
                      .html(msg);
        })
        .mouseout(function (){
            $("#help").fadeOut("fast");
        })
        .rules("add", {
            required: true,
            messages: {
                required: "É necessário informar se possui ou possuiu algum tipo de financiamento."
            }
        });


    $(".titularDeficienteFisico")
        .mouseover(function (){
            var msg = "<p>Informe se o solicitante possui algum tipo de deficiência ";
            msg+= "física, visual, mental, auditiva ou multipla. </p>";
            msg+= "<p>O candidado deverá apresentar laudo médico qua comprove";
            msg+= " a deficiência e que indique a Classificação Internacional";
            msg+= " de Doenças - CID</p>";
            msg+= "<p>Deverá ser observado o enquadramento na Portaria 610 de 26 de";
            msg+= " dezembro de 2011 do Ministério das Cidades, item 5.6, bem";
            msg+= " como as deliberações do Conselho Nacional da Pessoa com";
            msg+= " Deficiência";
            
            $("#help").fadeIn("fast")
                      .html(msg);
        })
        .mouseout(function (){
            $("#help").fadeOut("fast");
        })
        .rules("add", {
            required: true,
            messages: {
                required: "É necessário informar se possui deficiência física"
            }
        });


    $("#titularDataInscricao").rules("add", {
        required: true,
        minlength: 10,
        maxlength: 10,
        messages: {
            required: "A data de inscrição é obrigatório.",
            minlength: "A data de inscrição não é valida.",
            maxlength: "A data de inscrição não é valida."

        }
    });


    $("#titularNis")
        .focus(function (){
            var msg = "<p>Informe o NIS do solicitante. </p>";
            msg+= "<p> O NIS é o Número de Identificação Social. Aqueles que";
            msg+= " tem acesso a qualquer programa do Governo Federal, como";
            msg+= " Bolsa Família, devem preencher esse número na ficha de";
            msg+= " inscrição. Aqueles que ainda não possuem o NIS e trabalham";
            msg+= " com carteira assinada, devem utilizar o número do PIS.</p>";
            
            $("#help").fadeIn("fast")
                      .html(msg);
        })
        
        .mask('0000000000-0')

        .blur(function (){
            var nis = $(this).val();
            $.post('/application/index/nisunico',{nis: nis},function(data){
                if(data==="1"){
                    alert('Um titular com este número de NIS já foi cadastrado!');
                    $("#titularNis").val("");
                }
               
            });
        })
        .rules("add", {
            required: false,
            minlength: 12,
            maxlength: 12,
            messages: {
                minlength: "O NIS do titular não é valido.",
                maxlength: "O NIS do titular não é valido."

            }
        });


    $("#titularRenda")
        .mask("##0,00", {reverse: true, maxlength: false})
        
        .focus(function (){
            var msg = "<p>Informe a renda total do solicitante.</p>";
            msg+= "<p>A renda familiar deve considerar a renda formal (por exemplo,";
            msg+= " de ganhos salariais com carteira assinada) e ganhos informais";
            msg+= " (por exemplo, de ganhos de trabalho como ambulante). Os agentes";
            msg+= " financeiros pesquisam a renda dos membros das famílias em seus";
            msg+= " bancos de dados</p>";
            
            $("#help").fadeIn("fast")
                      .html(msg);
        })
        .blur(function (){
            $("#help").fadeOut("fast");
            if($(this).val() === ""){
                $(this).val(0);
            }
        })
        .rules("add", {
            required: true,
            minlength: 01,
            maxlength: 10,
            messages: {
                required: "A renda do titular é obrigatória.",
                minlength: "O NIS do titular não é valido.",
                maxlength: "O NIS do titular não é valido."
            }
        });

    /*
     * Validação dos telefones 
    */
    $('.telefoneTipo')
        .focus(function(){
            $("#help").fadeIn("fast")
                      .html("Selecione um tipo de telefone.");
        })
        .blur(function(){
            $("#help").fadeOut("fast");
        });
    
    $('.telefoneNumero')
        .focus(function(){
            $("#help").fadeIn("fast")
                      .html("Preencha o DDD+telefone. (Somente números)");
    });
    
    $('input[name="telefones[0][numero]"]').keydown(function(){
        if($('select[name="telefones[0][tipoTelefone]"]').val() === "3"){
            $('input[name="telefones[0][numero]"]')
                .mask("(00)00000-0000")
                .rules("add", {
                    required: true,
                    maxlength: 14,
                    messages: {
                        maxlength: 'O campo "Telefone" não é valido.'
                }
            });
        }else{
            $('input[name="telefones[0][numero]"]')
                .mask("(00)0000-0000")
                .rules("add", {
                    required: true,
                    maxlength: 13,
                    messages: {
                        maxlength: 'O campo "Telefone" não é valido.'
                }
            });
        }
    });

    $('input[name="telefones[1][numero]"]').keydown(function(){
        if($('select[name="telefones[1][tipoTelefone]"]').val() === "3"){
            $('input[name="telefones[1][numero]"]')
                .mask("(00)00000-0000")
                .rules("add", {
                    required: false,
                    maxlength: 14,
                    messages: {
                        maxlength: 'O campo "Telefone" não é valido.'
                }
            });
        }else{
            $('input[name="telefones[1][numero]"]')
                .mask("(00)0000-0000")
                .rules("add", {
                    required: false,
                    maxlength: 13,
                    messages: {
                        maxlength: 'O campo "Telefone" não é valido.'
                }
            });
        }
    });
    
    $('input[name="telefones[2][numero]"]').keydown(function(){
        if($('select[name="telefones[2][tipoTelefone]"]').val() === "3"){
            $('input[name="telefones[2][numero]"]')
                .mask("(00)00000-0000")
                .rules("add", {
                    required: false,
                    maxlength: 14,
                    messages: {
                        maxlength: 'O campo "Telefone" não é valido.'
                }
            });
        }else{
            $('input[name="telefones[2][numero]"]')
                .mask("(00)0000-0000")
                .rules("add", {
                    required: false,
                    maxlength: 13,
                    messages: {
                        maxlength: 'O campo "Telefone" não é valido.'
                }
            });
        }
    });
    /*
     * Validação Fieldset conjuge
     */


    $("#conjugeNome")
        .focus(function (){
            $("#help").fadeIn("fast")
                .text("Preencha o nome completo do conjuge do solicitante.");
        })
        .blur(function (){
            $("#help").fadeOut("fast");
        })
        .rules("add", {
            required: true,
            minlength: 3,
            maxlength: 200,
            messages: {
                minlength: "O nome do Conjuge deve haver no mínimo três caracteres.",
                maxlength: "O nome do Conjuge deve haver no máximo duzentos caracteres."
            }
        });


    $("#conjugeCpf")
        .mask('000.000.000-00')
        
        .focus(function (){
            $("#help").fadeIn("fast")
                      .text("Preencha o CPF do conjuge. (Somente números!)");
        })
        .blur(function (){
            if($(this).val() === $("#titularCpf").val()){
                alert("O CPF do conjuge não pode ser igual ao do titular.")
                $(this).val("");
            }
            $("#help").fadeOut("fast");
        })
        
        .rules("add", {
            required: true,
            cpf: "cpf",
            minlength: 14,
            maxlength: 14,
            messages: {
                cpf: "cpf",
                minlength: "O CPF do conjuge possui tamanho inválido.",
                maxlength: "O CPF do conjuge possui tamanho inválido."

            }
        });


    $("#conjugeDataNascimento")
        .mask('00/00/0000')

        .focus(function (){
            $("#help").fadeIn("fast")
                .text("Preencha a data de nascimento do conjuge.");
        })
        
        .blur(function (){
            $("#help").fadeOut("fast");
        })

        .rules("add", {
            required: false,
            dateBR: true,
            minlength: 10,
            maxlength: 10,
            messages: {
                dateBR: "Data de nascimento do conjuge inválida",
                minlength: "A data de nascimento do conjuge não é valida.",
                maxlength: "A data de nascimento do conjuge não é valida."

            }
        });

     $("#conjugeNaturalidade")
        .focus(function (){
            $("#help").fadeIn("fast")
                .text("Selecione em que cidade o conjuge do solicitante nasceu.");
        })
        .blur(function (){
            $("#help").fadeOut("fast");
        })
        .rules("add", {
        
            required: true,
           
            messages: {
                required: "A naturalidade do Conjuge é obrigatório.",
           
            }
        });

     $("#conjugeSexo")
        .focus(function (){
            $("#help").fadeIn("fast")
                .text("Selecione o sexo do conjuge.");
        })
        .blur(function (){
            $("#help").fadeOut("fast");
        })
        .rules("add", {
        
            required: true,
           
            messages: {
                required: "O sexo do conjuge é obrigatório.",
           
            }
        });    
    
    $(".conjugeAcolhimentoInstitucional")
        .mouseover(function (){
            var msg = "<p>Informe se o conjuge se encontra em abrigo ";
            msg+= "institucional. </p>";
            msg+= "<p>Considera-se a situação de acolhimento institucional ";
            msg+= " àquelas famílias que possuem algum de seus membros acolhidos";
            msg+= " pela rede socioassistencial ou por instituições prisionais.</p>";
            
            $("#help").fadeIn("fast")
                      .html(msg);
        })
        .mouseout(function (){
            $("#help").fadeOut("fast");
        })
        .rules("add", {
            required: true,
            messages: {
                required: "É necessário informar se o titular se encontra em abrigo institucional."
            }
        });

    $("#conjugeDeficienteFisico")
        .mouseover(function (){
            var msg = "<p>Informe se o conjuge possui algum tipo de deficiência ";
            msg+= "física, visual, mental, auditiva ou multipla. </p>";
            msg+= "<p>O candidado deverá apresentar laudo médico qua comprove";
            msg+= " a deficiência e que indique a Classificação Internacional";
            msg+= " de Doenças - CID</p>";
            msg+= "<p>Deverá ser observado o enquadramento na Portaria 610 de 26 de";
            msg+= " dezembro de 2011 do Ministério das Cidades, item 5.6, bem";
            msg+= " como as deliberações do Conselho Nacional da Pessoa com";
            msg+= " Deficiência";
            
            $("#help").fadeIn("fast")
                      .html(msg);
        })
        .mouseout(function (){
            $("#help").fadeOut("fast");
        })
        .rules("add", {
            required: false,
        });


    $("#conjugeNis")
        .mask('0000000000-0')
        
        .focus(function (){
            var msg = "<p>Informe o NIS do solicitante. </p>";
            msg+= "<p> O NIS é o Número de dadeficação Social. Aqueles que";
            msg+= " tem acesso a qualquer programa do Governo Federal, como";
            msg+= " Bolsa Família, devem preencher esse número na ficha de";
            msg+= " inscrição. Aqueles que ainda não possuem o NIS e trabalham";
            msg+= " com carteira assinada, devem preencher com número do PIS.</p>";
            
            $("#help").fadeIn("fast")
                      .html(msg);
        })
        
        .rules("add", {
            required: false,
            minlength: 12,
            maxlength: 12,
            messages: {
                minlength: "O NIS do conjuge não é valido.",
                maxlength: "O NIS do conjuge não é valido."

            }
        });


    $("#conjugeRenda")
        .mask("##0,00", {reverse: true, maxlength: false})
        
        .focus(function (){
            var msg = "<p>Informe a renda total do conjuge.</p>";
            msg+= "<p>A renda familiar deve considerar a renda formal (por exemplo,";
            msg+= " de ganhos salariais com carteira assinada) e ganhos informais";
            msg+= " (por exemplo, de ganhos de trabalho como ambulante). Os agentes";
            msg+= " financeiros pesquisam a renda dos membros das famílias em seus";
            msg+= " bancos de dados</p>";
            
            $("#help").fadeIn("fast")
                      .html(msg);
        })
        .blur(function (){
            $("#help").fadeOut("fast");
            if($(this).val() === ""){
                $(this).val(0);
            }
        })
        
        .rules("add", {
            required: true,
            minlength: 1,
            maxlength: 10,
            messages: {
                minlength: "A renda do conjuge não é valida.",
                maxlength: "A renda do conjuge não é valida."

            }
        });


    /*
     * Validação Fieldset Identidade Titular
     */

    $("#identidadeTitularNumero")
    
        .focus(function (){
            $("#help").fadeIn("fast")
                      .text("Preencha o número da carteira de identidade do solicitante.(Somente números)");
        })
        .blur(function (){
            var rg = $(this).val();
            $.post('/application/index/rgunico',{rg: rg},function(data){
                if(data==="1"){
                    alert('Um solicitante com este número de identidade já foi cadastrado!');
                    $("#identidadeTitularNumero").val("");
                }
               
            });
            
            $("#help").fadeOut("fast");
        })
        
        .rules("add", {
            required: true,
            minlength: 6,
            maxlength: 15,
            messages: {
                required: "A identidade do titular é obrigatório.",
                minlength: "O numero da identidade do titular não é valido.",
                maxlength: "O numero da identidade do titular não é valido."

            }
        });


    $("#identidadeTitularDataEmissao")
        .mask('00/00/0000')
        
        .focus(function (){
            $("#help").fadeIn("fast")
                      .text("Preencha a data de emissão da identidade do titular.");
        })
        
        .blur(function (){
            $("#help").fadeOut("fast");
        })
                
        .rules("add", {
            required: true,
            dateBR: true,
            minlength: 10,
            maxlength: 10,
            messages: {
                dateBR: "Data de emissão da identidade do titulat inválida.",
                required: "A data de emissão da Identidade é obrigatório.",
                minlength: "A data de emissão da Identidade não é valida.",
                maxlength: "A data de emissão da Identidade não é valida."

            }
        });

    $("#identidadeTitularOrgaoEmissor")
        .focus(function (){
            $("#help").fadeIn("fast")
                      .text("Preencha o orgão emissor da carteira de identidade do titular.");
        })
        .blur(function (){
            $("#help").fadeOut("fast");
        })
        .rules("add", {
            required: true,
            minlength: 2,
            maxlength: 200,
            messages: {
                required: "O orgão emissor da Identidade é obrigatório.",
                minlength: "O orgão emissor da Identidade não é valido.",
                maxlength: "O orgão emissor da Identidade não é valido."
            }
        });


    /*
     * Validação Fieldset Identidade Conjuge
     */

    $("#identidadeConjugeNumero")
        .focus(function (){
            $("#help").fadeIn("fast")
                      .text("Preencha o número da carteira de identidade do conjuge. (Somente números)");
        })
        .blur(function (){
            $("#help").fadeOut("fast");
        })

        .rules("add", {
            required: true,
            minlength: 6,
            maxlength: 15,
            messages: {
                minlength: "O numero da identidade do conjuge não é valido.",
                maxlength: "O numero da identidade do conjuge não é valido."
            }
        });


    $("#identidadeConjugeDataEmissao")
        .mask('00/00/0000')
        
        .focus(function (){
            $("#help").fadeIn("fast")
                      .text("Preencha a data de emissão da identidade do conjuge.");
        })
        
        .blur(function (){
    
            $("#help").fadeOut("fast");
        })
        
        .rules("add", {
        required: true,
        dateBR: true,
        minlength: 10,
        maxlength: 10,
        messages: {
            dateBR: "Data de emissão da identidade do conjuge inválida.",
            minlength: "A data de emissão da identidade do conjuge não é valida.",
            maxlength: "A data de emissão da Identidade do conjuge não é valida."
        }
    });

    $("#identidadeConjugeOrgaoEmissor")
        .focus(function (){
            $("#help").fadeIn("fast")
                      .text("Preencha o orgão emissor da carteira de identidade do conjuge.");
        })
        .blur(function (){
            $("#help").fadeOut("fast");
        })
        .rules("add", {
        required: true,
        minlength: 2,
        maxlength: 200,
        messages: {
            minlength: "O orgão emissor da identidade do conjuge não é valido.",
            maxlength: "O orgão emissor da identidade do conjuge não é valido."

        }
    });


    /*
     * Validação Fieldset endereço
     */

    $("#enderecoTipoLogradouro")
        .focus(function (){
            $("#help").fadeIn("fast")
                      .text("Selecione o tipo de logradouro do endereço do solicitante.");
        })
        .blur(function (){
            $("#help").fadeOut("fast");
        })
        .rules("add", {
            required: true,
            messages: {
                required: "Informe o tipo de logradouro."
            }
        });


    $("#enderecoNomeLogradouro")
        .focus(function (){
            $("#help").fadeIn("fast")
                      .text("Preencha o endereço do solicitante.");
        })
        .blur(function (){
            $("#help").fadeOut("fast");
        })
        .rules("add", {
            required: true,
            minlength: 2,
            maxlength: 200,
            messages: {
                required: "Informe o tipo de logradouro.",
                minlength: "O nome do logradouro não é valido.",
                maxlength: "O nome do logradouro não é valido."
            }
        });

    $("#enderecoNumero")
        .mask('#', {maxlength: false})
        .focus(function (){
            $("#help").fadeIn("fast")
                      .text("Preencha o número da residência do solicitante.");
        })
        .blur(function (){
            $("#help").fadeOut("fast");
        })
        .rules("add", {
        required: false,
        minlength: 0,
        maxlength: 10,        
        messages: {
            required: "Informe o numero.",
            minlength: "O número não é valido.",
            maxlength: "O número não é valido."            
        }
    });


    $("#enderecoComplemento")
        .focus(function (){
            $("#help").fadeIn("fast")
                      .text("Caso exista, preencha o complemento do endereço do solicitante.");
        })
        .blur(function (){
            $("#help").fadeOut("fast");
        })
        .rules("add", {
            required: false,
            minlength: 5,
            maxlength: 200,
            messages: {
                minlength: "O complemento do logradouro não é valido.",
                maxlength: "O complemento do logradouro não é valido."
            }
        });


    $("#enderecoBairro")
        .focus(function (){
            $("#help").fadeIn("fast")
                      .text("Preencha o bairro do solicitante.");
        })
        .blur(function (){
            $("#help").fadeOut("fast");
        })
        .rules("add", {
            required: true,
            minlength: 3,
            maxlength: 200,
            messages: {
                required: "Informe o bairro.",
                minlength: "O bairro não é valido.",
                maxlength: "O bairro não é valido."
            }
        });


    $("#enderecoComunidade")
        .focus(function (){
            $("#help").fadeIn("fast")
                      .text("Caso resida em alguma comunidade, preencha o nome da mesma.");
        })
        .blur(function (){
            $("#help").fadeOut("fast");
        })
        .rules("add", {
            required: false,
            minlength: 3,
            maxlength: 200,
            messages: {
                minlength: "A comunidade não é valida.",
                maxlength: "A comunidade não é valida."
            }
        });


    $("#enderecoDistrito")
        .focus(function (){
            $("#help").fadeIn("fast")
                      .text("Selecione o distrito onde reside o solicitante.");
        })
        .blur(function (){
            $("#help").fadeOut("fast");
        })
        .rules("add", {
            required: true,
            messages: {
                required: "Informe o distrito."
            }
        });


    $("#enderecoCep")
        .mask('00000-000')
        .focus(function (){
            $("#help").fadeIn("fast")
                      .text("Preencha o CEP do solicitante.");
        })
        .blur(function (){
            $("#help").fadeOut("fast");
        })
        .rules("add", {
            required: true,
            minlength: 9,
            maxlength: 9,
            messages: {
                required: "Informe o CEP.",
                minlength: "O CEP não é valido.",
                maxlength: "O CEP não é valido."
            }
        });


    $(".enderecoAreaDeRisco")
        .mouseover(function (){
            var msg = "<p>Considera-se como família residente em área de risco aquelas";
            msg+= " que moram em áreas que apresentam risco geológico ou de insalubridade,";
            msg+= " tais como erosão, solapamento, queda e rolamento de blocos de";
            msg+= " rocha, eventos de inundação, taludes, barrancos, áreas declivosas,";
            msg+= " encostas sujeitas a desmoronamento e lixões, áreas que margeam";
            msg+= " rios, córrego ou quaisquer cursos d'água, áreas contaminadas ou";
            msg+= " poluídas, observando-se legislação ambiental específica, de";
            msg+= " acordo com o Plano Municipal de Redução de Riscos, Plano Diretor";
            msg+= " ou determinação da Defesa Civil de Duque de Caxias.";
            msg+= " Aos moradores de áreas de risco poderá ser exigida a doação"; 
            msg+= "do seu imóvel para possibilitar a sua demolição.</p>";
            
            $("#help").fadeIn("fast")
                      .html(msg);
        })
        .mouseout(function (){
            $("#help").fadeOut("fast");
        })
        .rules("add", {
            required: true,
            messages: {
                required: "Informe se é área de risco."
            }
        });

    });

/*
 * Validação Fieldset Dependente
 */

function helpDependenteNome() {
        $("#help").fadeIn("fast")
                  .text("Preencha o nome completo do dependente do solicitante.");
}

function dependenteNomeToUpper(){
    $('.dependenteNome').each(function(){
        $(this).val($(this).val().toUpperCase());
    });
}

function validarDependenteNome() {
    $(".dependenteNome")
        .rules("add", {
        required: false,
        minlength: 3,
        maxlength: 200,
        messages: {
            minlength: "O nome do Dependente deve haver no mínimo três caracteres.",
            maxlength: "O nome do Dependente deve haver no máximo duzentos caracteres."

        }
    });
    
    $("#help").fadeOut("fast");
    dependenteNomeToUpper();
}

function helpDependenteCpf() {
        $("#help").fadeIn("fast")
                  .text("Preencha o cpf do dependente do solicitante.");
}

function validarDependenteCpf() {
    $('.dependenteCpf')
        .mask('000.000.000-00',{reverse: true})
        .rules("add", {
            required: false,
            cpf: "cpf",
            minlength: 14,
            maxlength: 14,
            messages: {
                cpf: "O cpf do dependente é inválido!",
                minlength: "O CPF do Dependente não é valido.",
                maxlength: "O CPF do Dependente não é valido."
            }
        });
    
    $("#help").fadeOut("fast");
}

function helpDependenteGrauDeParentesco() {
        $("#help").fadeIn("fast")
                  .text("Selecione o grau de parentesco entre dependente e o solicitante.");
}

function validarDependenteGrauDeParentesco(){
    $(".dependenteGrauDeParentesco").rules("add", {
        required: false,
    });
    
    $("#help").fadeOut("fast");
}

function helpDependenteDataNascimento() {
        $("#help").fadeIn("fast")
                  .text("Preencha a data de nascimento do dependente.");
}

function mascararDependenteDataNascimento(){
    $(".dependenteDataNascimento")
        .mask('00/00/0000');
}

function validarDependenteDataNascimento(){
    $(".dependenteDataNascimento")
        .mask('00/00/0000')
        .rules("add", {
            required: false,
            dateBR: true,
            minlength: 10,
            maxlength: 10,
            messages: {
                dateBR: "Data de nascimento do dependente inválida.",
                minlength: "A data de nascimento do Dependente não é valida.",
                maxlength: "A data de nascimento do Dependente não é valida."

            }
    });
    $("#help").fadeOut("fast");        
}

function helpDependenteAbrigoInstitucional(){
    var msg = "<p>Informe se o familiar se encontra em abrigo ";
            msg+= "institucional. </p>";
            msg+= "<p>Considera-se a situação de acolhimento institucional ";
            msg+= " àquelas famílias que possuem algum de seus membros acolhidos";
            msg+= " pela rede socioassistencial ou por instituições prisionais.</p>";
            
            $("#help").fadeIn("fast")
                      .html(msg);
}

function helpDependenteAbrigoInstitucionalOut(){
    $("#help").fadeOut("fast");
}

function validarDependenteAbrigoInstitucional(){
    $(".titularAcolhimentoInstitucional")
        .rules("add", {
            required: false,
        });
}

function helpDependenteDeficienteFisico(){
        var msg = "<p>Informe se o dependente possui algum tipo de deficiência ";
            msg+= "física, visual, mental, auditiva ou multipla. </p>";
            msg+= "<p>O candidado deverá apresentar laudo médico qua comprove";
            msg+= " a deficiência e que indique a Classificação Internacional";
            msg+= " de Doenças - CID</p>";
            msg+= "<p>Deverá ser observado o enquadramento na Portaria 610 de 26 de";
            msg+= " dezembro de 2011 do Ministério das Cidades, item 5.6, bem";
            msg+= " como as deliberações do Conselho Nacional da Pessoa com";
            msg+= " Deficiência";
            
            $("#help").fadeIn("fast")
                      .html(msg);
}

function helpDependenteDeficienteFisicoOut(){           
            $("#help").fadeOut("fast");
}

function validarDependenteDeficienteFisico(){
    $(".dependenteDeficienteFisico").rules("add", {
        required: false,
    });
    
    $("#help").fadeOut("fast");
}

function helpDependenteRenda(){
        var msg = "<p>Informe a renda total do dependente.</p>";
            msg+= "<p>A renda familiar deve considerar a renda formal (por exemplo,";
            msg+= " de ganhos salariais com carteira assinada) e ganhos informais";
            msg+= " (por exemplo, de ganhos de trabalho como ambulante). Os agentes";
            msg+= " financeiros pesquisam a renda dos membros das famílias em seus";
            msg+= " bancos de dados</p>";
            
        $("#help").fadeIn("fast")
                  .html(msg);
}

function validarDependenteRenda(){
    $(".dependenteRenda").mask("##0,00", {reverse: true, maxlength: false}).rules("add", {
        required: false,
        minlength: 1,
        maxlength: 10,
        messages: {
            minlength: 'O campo "renda do dependente" não é valido.',
            maxlength: 'O campo "renda do dependente" não é valido.'

        }
    });
    
    $("#help").fadeOut("fast");
}

function helpDependente(){
        $("#help").fadeIn("fast")
                  .text("Clique aqui para adicionar um familiar que mora com você.");
}

function helpDependenteOut(){
        $("#help").fadeOut("fast");
}