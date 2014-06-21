Um módulo para utilizar o sistema de pagamento da Redecard direto no Magento, de graça e com código aberto.

## Características:

* O cliente digita os dados do cartão no próprio checkout do magento, sem precisar ser redirecionado para nenhum site
* Possibilidade de parcelamento sem juros
* Possibilidade de desconto para pagamento à vista no cartão;
* Não necessita de gateway, o pagamento é feito diretamente por webservice, no site da redecard
* Mensagens de erro com o motivo pelo qual o pagamento por cartão não foi aprovado
* Valor mínimo de cada parcela
* Aprovação imediata
* Segurança

O módulo é bastante seguro. O único dado do cartão que fica arquivado na loja é o nome do cliente e os ultimos 4 dígitos.

## Instalação

A instalação é extremamente simples, basta descompactar os arquivos enviados e colar na pasta raiz do magento.

Para instalar é necessário apenas ter a versão 1.5, 1.6 ou superior do magento instalado.


# FAQ
<dl>

<dt>Posso instalar o módulo em mais de uma loja?</dt>
	<dd> Sim, esse é um projeto 100% opensource.</dd>

<dt>Quanto tempo dura uma licença?</dt>
	<dd>Por tempo ilimitado! A licença nunca expira.</dd>

<dt>Posso receber pagamento por cartão de débito por esse módulo?</dt>
	<dd>Não. Este módulo é apenas para os cartões de crédito visa, mastercard e diners.</dd>

<dt>É preciso fazer algum cadastro no site da redecard?</dt>
	<dd>Sim. É preciso ser filiado e ter o cadastro para acessar o komerci por webservice.</dd>

<dt>Posso comercializar o Multikomerci?</dt>
	<dd>Não! O módulo é gratuito, de modo algum ele pode ser comercializado.</dd>

<dt>Existe um risco em vender direto pelo cartão, sem intermediário ou gateways?</dt>
	<dd>Existe sim, mas não um risco técnico e sim um risco de fraudes que possam vir a ocorrer em sua loja.
	Por exemplo, se um cartão é roubado e é feita uma compra com ele, para ser entregue num endereço desconhecido. Neste caso recomendamos a contratação de um sistema de analise de risco (fcontrol).</dd>

<dt>Quais os benefícios de vender direto pelo cartão?</dt>
	<dd>São muitos! Passa mais confiança para os compradores, fica livre das mensalidades dos gateways e dos juros absurdos dos intermediários.</dd>

<dt>O komerci da redecard tem mensalidade?</dt>
	<dd>Não! O komerci é gratuito, basta efetuar a filiação para vendas pela internet.</dd>

<dt>O que é AVS? O módulo tem suporte a isso?</dt>
<dd>AVS é um serviço oferecido pela redecard que confere os dados do cartão do cliente da sua loja, com os dados que ele cadastrou para entrega, além de seus dados pessoais. A redecard oferece esse serviço atualmente apenas para os cartões mastercard e dinners.
	
	O módulo ainda não oferece suporte a esse serviço.</dd>

<dt>Como funciona a homologação do módulo junto a redecard?</dt>
<dd>A redecard não exige homologação! Após a instalção do módulo, o sistema de pagamento já estará em produção. Para testá-lo, basta criar um produto de R$ 0,01 e efetuar uma compra com um cartão válido. É bastante simples.</dd>

</dl>


## Log de mudanças

### Versão Atual: 1.2.6

#### Data da Última Alteração: 08/08/2012

##### Alteração na versão 1.2.6
- Adicionada função que envia e-mail após a confirmação da compra
- Correção de arquivo xml do tema

##### Alteração na versão 1.2.5
- Mudança no nome do módulo. De Multikomerci para Multikomerce Redecard
- Alteração da estrutura de diretórios e imagem das bandeiras

##### Alteração na versão 1.2.4
- Correção do redirecionamento do carrinho de compras após o checkout

##### Alteração na versão 1.2.3
- Correção do tamanho das bandeiras dos cartões
- Correção do host para envio dos dados da redecard
Alteração na versão 1.2.1
- Correção no bug que retornava “dados inválidos” em compras acima de mil reais

##### Alteração na versão 1.2.0
- Correção do problema na exibição do formulário com dados do cartão no checkout padrão do magento

##### Principais Novidades da versão 1.1.0
- Alteração da página de confirmação. Agora a confirmação é a padrão do magento, isso possibilita o uso da função e-commerce do google analytics;
- Modificação das informações do pagamento exibidas para o cliente na “Área do cliente”;
- Faturamento automatico. Quando o pagamento é confirmado pela redcard, o magento automaticamente faz o faturamento do pedido, mudando seu estatus;
- Algumas alterações no template que estava com problema no checkout;
- Otimização do código, retirando alguns ‘echos’ perdidos durante o desenvolvimento.