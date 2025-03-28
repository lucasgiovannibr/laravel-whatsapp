# Tipos de Mensagens Suportados

O pacote Laravel WhatsApp suporta diversos tipos de mensagens para atender às necessidades da sua aplicação. Cada tipo de mensagem possui características específicas e estrutura própria para envio.

## Mensagens de Texto

A forma mais simples de comunicação, ideal para mensagens diretas e informativas.

```php
WhatsApp::sendText('5548999998888', 'Olá, como podemos ajudar hoje?');
```

Parâmetros:
- `$to` (string): Número de telefone do destinatário com código do país
- `$message` (string): Conteúdo da mensagem
- `$sessionId` (string|null): ID da sessão WhatsApp (opcional)

## Templates de Mensagens

Templates permitem personalizar mensagens com dados dinâmicos usando a sintaxe Handlebars.

```php
WhatsApp::sendTemplate('5548999998888', 'confirmacao_pedido', [
    'nome' => 'João Silva',
    'numero_pedido' => '123456',
    'data_entrega' => '28/10/2023',
    'valor' => 'R$ 249,90'
]);
```

Parâmetros:
- `$to` (string): Número de telefone do destinatário
- `$templateName` (string): Nome do template previamente cadastrado
- `$data` (array): Dados para preencher o template
- `$sessionId` (string|null): ID da sessão (opcional)

## Mensagens com Imagens

Envie imagens com legenda opcional para ilustrar seus produtos ou serviços.

```php
WhatsApp::sendImage('5548999998888', 'https://exemplo.com/produto.jpg', 'Confira nosso novo produto!');

// Ou com um arquivo local
WhatsApp::sendImage('5548999998888', storage_path('app/public/produtos/item.jpg'));
```

Parâmetros:
- `$to` (string): Número do destinatário
- `$imageUrl` (string): URL ou caminho local da imagem
- `$caption` (string|null): Legenda da imagem (opcional)
- `$sessionId` (string|null): ID da sessão (opcional)

## Mensagens com Arquivos

Compartilhe documentos, PDFs, planilhas e outros tipos de arquivos.

```php
WhatsApp::sendFile('5548999998888', 'https://exemplo.com/relatorio.pdf', 'Relatório_Mensal.pdf');
```

Parâmetros:
- `$to` (string): Número do destinatário
- `$fileUrl` (string): URL ou caminho local do arquivo
- `$filename` (string|null): Nome do arquivo que será exibido (opcional)
- `$sessionId` (string|null): ID da sessão (opcional)

## Mensagens com Localização

Compartilhe endereços e pontos de referência com coordenadas geográficas.

```php
WhatsApp::sendLocation('5548999998888', -27.5969, -48.5495, 'Nossa Loja em Florianópolis');
```

Parâmetros:
- `$to` (string): Número do destinatário
- `$latitude` (float): Latitude da localização
- `$longitude` (float): Longitude da localização
- `$title` (string|null): Título da localização (opcional)
- `$sessionId` (string|null): ID da sessão (opcional)

## Mensagens com Botões

Apresente opções de resposta rápida ao usuário através de botões.

```php
WhatsApp::sendButtons(
    '5548999998888',
    'Confirmação de Pedido',
    'Seu pedido #12345 foi confirmado! O que deseja fazer?',
    [
        ['id' => 'rastrear', 'text' => 'Rastrear Pedido'],
        ['id' => 'cancelar', 'text' => 'Cancelar Pedido'],
        ['id' => 'suporte', 'text' => 'Falar com Suporte']
    ]
);
```

Parâmetros:
- `$to` (string): Número do destinatário
- `$title` (string): Título da mensagem
- `$message` (string): Corpo da mensagem
- `$buttons` (array): Lista de botões com id e texto
- `$sessionId` (string|null): ID da sessão (opcional)

## Mensagens com Lista (Novo!)

Crie listas interativas com seções e opções para seleção, ideais para apresentar catálogos, menus ou outras opções organizadas.

```php
WhatsApp::sendList(
    '5548999998888',
    'Catálogo de Produtos',
    'Por favor, escolha uma categoria para ver nossos produtos',
    'Ver Categorias',
    [
        [
            'title' => 'Eletrônicos',
            'rows' => [
                ['id' => 'celulares', 'title' => 'Celulares', 'description' => 'Smartphones e acessórios'],
                ['id' => 'computadores', 'title' => 'Computadores', 'description' => 'Notebooks e desktops']
            ]
        ],
        [
            'title' => 'Roupas',
            'rows' => [
                ['id' => 'masculino', 'title' => 'Masculino', 'description' => 'Camisas, calças e mais'],
                ['id' => 'feminino', 'title' => 'Feminino', 'description' => 'Vestidos, blusas e mais']
            ]
        ]
    ]
);
```

Parâmetros:
- `$to` (string): Número do destinatário
- `$title` (string): Título principal da lista
- `$description` (string): Descrição que aparece no corpo da mensagem
- `$buttonText` (string): Texto do botão que exibe a lista
- `$sections` (array): Array de seções, cada uma com título e linhas
- `$sessionId` (string|null): ID da sessão (opcional)

Estrutura de uma seção:
```php
[
    'title' => 'Título da Seção',
    'rows' => [
        [
            'id' => 'ID_único', // Identificador único para esta opção
            'title' => 'Título da Opção', // Título principal da opção
            'description' => 'Descrição' // Descrição opcional da opção
        ],
        // Mais opções...
    ]
]
```

## Mensagens com Enquete (Novo!)

Crie enquetes para coletar opiniões, fazer votações ou realizar pesquisas diretamente no WhatsApp.

```php
WhatsApp::sendPoll(
    '5548999998888',
    'Qual o melhor dia para nossa reunião?',
    [
        'Segunda-feira às 14h',
        'Terça-feira às 10h',
        'Quarta-feira às 16h',
        'Quinta-feira às 9h',
        'Sexta-feira às 15h'
    ],
    false // escolha única
);
```

Parâmetros:
- `$to` (string): Número do destinatário
- `$question` (string): Pergunta da enquete
- `$options` (array): Lista de opções para votação (máximo 12 opções)
- `$isMultiSelect` (bool): Se verdadeiro, permite seleção múltipla
- `$sessionId` (string|null): ID da sessão (opcional)

## Mensagens com Produtos (Novo!)

Envie produtos do seu catálogo no WhatsApp Business, incluindo imagens, descrições e preços.

```php
WhatsApp::sendProduct(
    '5548999998888',
    'catalog_id_123',
    'product_id_456'
);
```

Parâmetros:
- `$to` (string): Número do destinatário
- `$catalogId` (string): ID do catálogo de produtos
- `$productId` (string): ID do produto específico
- `$sessionId` (string|null): ID da sessão (opcional)

> **Importante**: Para utilizar esta funcionalidade, é necessário ter um catálogo de produtos cadastrado em sua conta WhatsApp Business.

## Mensagens com Catálogo (Novo!)

Envie uma seção do seu catálogo de produtos, permitindo que o cliente veja múltiplos itens de uma só vez.

```php
WhatsApp::sendCatalog(
    '5548999998888',
    'catalog_id_123',
    [
        'product_id_456',
        'product_id_789',
        'product_id_101'
    ]
);
```

Parâmetros:
- `$to` (string): Número do destinatário
- `$catalogId` (string): ID do catálogo de produtos
- `$productItems` (array): Lista de IDs de produtos do catálogo
- `$sessionId` (string|null): ID da sessão (opcional)

## Mensagens com Pedidos (Novo!)

Crie e envie pedidos completos, com lista de produtos, quantidades, preços e informações de pagamento.

```php
WhatsApp::sendOrder(
    '5548999998888',
    [
        'order_id' => '123456', // Identificador único do pedido
        'catalog_id' => 'catalog_id_123', // ID do catálogo
        'items' => [
            [
                'product_id' => 'product_id_456',
                'quantity' => 2,
                'price' => 99.90
            ],
            [
                'product_id' => 'product_id_789',
                'quantity' => 1,
                'price' => 149.90
            ]
        ],
        'total' => 349.70, // Valor total do pedido
        'currency' => 'BRL', // Moeda (BRL, USD, EUR, etc.)
        'payment_method' => 'PIX', // Método de pagamento
        'additional_info' => 'Pedido com entrega expressa', // Informações adicionais
        'address' => [
            'street' => 'Rua das Flores, 123',
            'city' => 'Florianópolis',
            'state' => 'SC',
            'zipcode' => '88000-000'
        ]
    ]
);
```

Parâmetros:
- `$to` (string): Número do destinatário
- `$orderData` (array): Dados completos do pedido
- `$sessionId` (string|null): ID da sessão (opcional)

Estrutura de `$orderData`:
- `order_id`: Identificador único do pedido
- `catalog_id`: ID do catálogo de produtos
- `items`: Array de itens do pedido, cada um com:
  - `product_id`: ID do produto
  - `quantity`: Quantidade
  - `price`: Preço unitário
- `total`: Valor total do pedido
- `currency`: Código da moeda (BRL, USD, EUR, etc.)
- `payment_method`: Método de pagamento
- `additional_info`: Informações adicionais (opcional)
- `address`: Endereço de entrega (opcional)

## Recebendo Respostas

Para cada tipo de mensagem interativa (botões, listas, enquetes, produtos), você poderá receber diferentes tipos de respostas do usuário. Essas respostas são processadas através do sistema de webhooks.

### Exemplo de resposta para lista:

```php
Event::listen(WhatsAppMessageReceived::class, function (WhatsAppMessageReceived $event) {
    $message = $event->message;
    
    if ($message['type'] === 'list_response') {
        $selectedId = $message['list_response']['id'];
        $selectedTitle = $message['list_response']['title'];
        
        // Processar a seleção do usuário
        if ($selectedId === 'celulares') {
            WhatsApp::sendText($message['from'], 'Você selecionou Celulares. Aqui estão nossas ofertas...');
        }
    }
});
```

### Exemplo de resposta para enquete:

```php
Event::listen(WhatsAppMessageReceived::class, function (WhatsAppMessageReceived $event) {
    $message = $event->message;
    
    if ($message['type'] === 'poll_response') {
        $selectedOptions = $message['poll_response']['options'];
        
        // Processar as opções selecionadas
        WhatsApp::sendText($message['from'], 'Obrigado por sua participação na enquete!');
    }
});
```

## Limitações

- Listas: Máximo de 10 seções e 10 opções por seção
- Enquetes: Máximo de 12 opções
- Botões: Máximo de 3 botões
- Alguns recursos podem não estar disponíveis em todas as versões da API WhatsApp

## Melhores Práticas

1. **Mantenha mensagens concisas**: Mesmo com recursos interativos, mensagens mais curtas têm melhores taxas de engajamento.

2. **Use imagens de qualidade**: Para produtos, use imagens bem iluminadas e com bom enquadramento.

3. **Organize listas logicamente**: Agrupe itens semelhantes em seções para facilitar a navegação.

4. **Teste em dispositivos reais**: A aparência das mensagens pode variar entre dispositivos iOS e Android.

5. **Monitore respostas**: Analise como os usuários interagem com seus elementos interativos para otimizar futuras comunicações. 