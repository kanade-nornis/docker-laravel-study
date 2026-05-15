<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="referrer" content="no-referrer">
    <title>FF14 Market Board</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-900 min-h-screen p-4 md:p-8">
    <div class="max-w-4xl mx-auto">
        <header class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-slate-800 mb-2">FF14 Market Board Viewer</h1>
            <p class="text-slate-600">Universalis API を使用して最新のマーケット情報を取得します。</p>
        </header>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-8">
            <div class="space-y-6">
                <!-- World and Search Row -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-1">
                        <label for="world" class="block text-sm font-semibold text-slate-700 mb-1">World</label>
                        <input id="world" type="text" value="Yojimbo" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    </div>
                    <div class="md:col-span-2">
                        <label for="item_name" class="block text-sm font-semibold text-slate-700 mb-1">アイテム名で検索</label>
                        <div class="flex space-x-2">
                            <input id="item_name" type="text" placeholder="例: エクスポーション" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                            <button id="nameSearchBtn" class="bg-slate-700 hover:bg-slate-800 text-white font-semibold py-2 px-4 rounded-lg transition active:scale-95 shadow-sm whitespace-nowrap text-sm">
                                候補を探す
                            </button>
                        </div>
                    </div>
                    <div class="md:col-span-1">
                        <label for="item_id" class="block text-sm font-semibold text-slate-700 mb-1">Item ID</label>
                        <input id="item_id" type="number" value="33269" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    </div>
                </div>

                <div class="flex justify-center pt-2 border-t border-slate-100">
                    <button id="searchBtn" class="w-full md:w-1/2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-xl transition duration-200 ease-in-out transform active:scale-95 shadow-lg flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        マーケット情報を表示
                    </button>
                </div>
            </div>
        </div>

        <!-- Search Results Suggestion -->
        <div id="searchSuggestions" class="hidden bg-white rounded-xl shadow-lg border border-slate-200 mb-8 overflow-hidden animate-in fade-in slide-in-from-top-4 duration-300">
            <div class="bg-slate-50 px-4 py-2 border-b border-slate-200 flex justify-between items-center">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">検索結果の候補 (クリックで選択)</span>
                <button id="closeSuggestions" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="suggestionsList" class="max-h-64 overflow-y-auto divide-y divide-slate-100">
                <!-- JS で動的に挿入 -->
            </div>
            <div id="pagination" class="hidden bg-slate-50 px-4 py-3 border-t border-slate-200 flex items-center justify-between">
                <button id="prevPage" class="text-sm font-medium text-slate-600 hover:text-blue-600 disabled:opacity-30 disabled:cursor-not-allowed flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    前へ
                </button>
                <span id="pageInfo" class="text-xs font-bold text-slate-500 uppercase tracking-widest">Page 1</span>
                <button id="nextPage" class="text-sm font-medium text-slate-600 hover:text-blue-600 disabled:opacity-30 disabled:cursor-not-allowed flex items-center">
                    次へ
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
        </div>

        <div id="loading" class="hidden">
            <div class="flex items-center justify-center p-12">
                <div class="animate-spin h-8 w-8 border-4 border-blue-500 border-t-transparent rounded-full"></div>
                <span class="ml-3 text-slate-600 font-medium">データを取得中...</span>
            </div>
        </div>

        <div id="error" class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-8" role="alert">
            <span id="errorMessage"></span>
        </div>

        <div id="result" class="hidden space-y-8">
            <!-- Summary Card -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-800 px-6 py-4">
                    <h2 id="itemName" class="text-xl font-bold text-white">アイテム情報</h2>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center p-4 bg-blue-50 rounded-lg">
                        <div class="text-sm text-blue-600 font-semibold mb-1">最安値 (NQ)</div>
                        <div id="minPriceNQ" class="text-2xl font-bold text-slate-800">-</div>
                    </div>
                    <div class="text-center p-4 bg-orange-50 rounded-lg">
                        <div class="text-sm text-orange-600 font-semibold mb-1">最安値 (HQ)</div>
                        <div id="minPriceHQ" class="text-2xl font-bold text-slate-800">-</div>
                    </div>
                    <div class="text-center p-4 bg-green-50 rounded-lg">
                        <div class="text-sm text-green-600 font-semibold mb-1">直近平均価格</div>
                        <div id="avgPrice" class="text-2xl font-bold text-slate-800">-</div>
                    </div>
                </div>
            </div>

            <!-- Listings Table -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h3 class="font-bold text-slate-800">現在の出品</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-600 text-sm uppercase">
                                <th class="px-6 py-3 font-semibold">品質</th>
                                <th class="px-6 py-3 font-semibold">単価</th>
                                <th class="px-6 py-3 font-semibold">数量</th>
                                <th class="px-6 py-3 font-semibold">合計</th>
                                <th class="px-6 py-3 font-semibold">リテイナー</th>
                            </tr>
                        </thead>
                        <tbody id="listingsBody" class="divide-y divide-slate-100 text-slate-700">
                            <!-- JS で動的に挿入 -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Raw JSON Toggle -->
            <details class="group bg-slate-100 rounded-lg overflow-hidden">
                <summary class="list-none cursor-pointer px-4 py-2 text-sm text-slate-600 font-medium hover:bg-slate-200 transition">
                    Raw JSON Data を表示
                </summary>
                <div class="p-4 border-t border-slate-200">
                    <pre id="json" class="text-xs bg-slate-900 text-slate-300 p-4 rounded-lg overflow-x-auto"></pre>
                </div>
            </details>
        </div>
    </div>

    <script>
        const btn = document.getElementById('searchBtn');
        const nameSearchBtn = document.getElementById('nameSearchBtn');
        const worldInput = document.getElementById('world');
        const itemIdInput = document.getElementById('item_id');
        const itemNameInput = document.getElementById('item_name');
        const searchSuggestions = document.getElementById('searchSuggestions');
        const suggestionsList = document.getElementById('suggestionsList');
        const closeSuggestions = document.getElementById('closeSuggestions');
        const pagination = document.getElementById('pagination');
        const prevPageBtn = document.getElementById('prevPage');
        const nextPageBtn = document.getElementById('nextPage');
        const pageInfo = document.getElementById('pageInfo');

        let currentPage = 1;
        let lastSearchName = '';

        const loading = document.getElementById('loading');
        const error = document.getElementById('error');
        const errorMessage = document.getElementById('errorMessage');
        const result = document.getElementById('result');
        const jsonOutput = document.getElementById('json');

        const formatGil = (val) => new Intl.NumberFormat('ja-JP').format(val) + ' Gil';

        // アイテム名検索
        async function performNameSearch(name, page = 1) {
            if (!name) return;

            // UIの初期化
            nameSearchBtn.disabled = true;
            nameSearchBtn.textContent = '検索中...';
            prevPageBtn.disabled = true;
            nextPageBtn.disabled = true;

            try {
                const res = await fetch(`/api/items/search?name=${encodeURIComponent(name)}&page=${page}`);
                const data = await res.json();

                if (!res.ok || data.error) {
                    const msg = data.error || '検索に失敗しました';
                    const suggestion = data.suggestion ? `\n\n${data.suggestion}` : '';
                    throw new Error(msg + suggestion);
                }

                // 状態の更新
                lastSearchName = name;
                currentPage = parseInt(data.page) || page;

                renderSuggestions(data);
            } catch (e) {
                showError(e.message);
            } finally {
                nameSearchBtn.disabled = false;
                nameSearchBtn.textContent = '候補を探す';
            }
        }

        nameSearchBtn.addEventListener('click', () => performNameSearch(itemNameInput.value.trim(), 1));

        prevPageBtn.onclick = () => {
            if (currentPage > 1) performNameSearch(lastSearchName, currentPage - 1);
        };

        nextPageBtn.onclick = () => {
            performNameSearch(lastSearchName, currentPage + 1);
        };

        function renderSuggestions(data) {
            const items = data.items || [];
            suggestionsList.innerHTML = '';

            const dummyIcon = 'https://placehold.jp/24/334155/ffffff/32x32.png?text=？';

            if (items.length === 0) {
                suggestionsList.innerHTML = '<div class="p-4 text-center text-slate-500">候補が見つかりませんでした</div>';
                pagination.classList.add('hidden');
            } else {
                items.forEach(item => {
                    const div = document.createElement('div');
                    div.className = 'flex items-center p-3 hover:bg-blue-50 cursor-pointer transition border-b border-slate-50 last:border-0';
                    const iconUrl = item.icon && item.icon.startsWith('https://v2.xivapi.com/api/asset') ?
                        item.icon :
                        null;

                div.innerHTML = `
                    ${iconUrl
                        ? `<img src="${iconUrl}" loading="lazy" referrerpolicy="no-referrer" class="w-8 h-8 mr-3 rounded shadow-sm border border-slate-100" onerror="this.src='${dummyIcon}'; this.onerror=null;">`
                        : `<div class="w-8 h-8 mr-3 rounded shadow-sm border border-slate-100 bg-slate-100 mr-3 flex items-center justify-center text-slate-400 text-xs">？</div>`
                    }
                    <div class="flex-1 overflow-hidden">
                        <div class="font-bold text-slate-800 truncate">${item.name}</div>
                        <div class="text-xs text-slate-400">ID: ${item.id}</div>
                    </div>
                `;
                    div.onclick = () => {
                        itemIdInput.value = item.id;
                        itemNameInput.value = item.name;
                        searchSuggestions.classList.add('hidden');
                        btn.click();
                    };
                    suggestionsList.appendChild(div);
                });

                // Pagination UI
                const page = parseInt(data.page) || 1;
                pageInfo.textContent = `Page ${page}`;
                prevPageBtn.disabled = (page <= 1);
                nextPageBtn.disabled = !data.has_more;

                pagination.classList.remove('hidden');
                // リストの一番上へスクロール（多数の結果がある場合）
                suggestionsList.scrollTop = 0;
            }
            searchSuggestions.classList.remove('hidden');
        }

        closeSuggestions.onclick = () => searchSuggestions.classList.add('hidden');

        // マーケット情報取得
        btn.addEventListener('click', async () => {
            const world = worldInput.value.trim();
            const itemId = itemIdInput.value.trim();

            if (!world || !itemId) {
                showError('World と Item ID を入力してください');
                return;
            }

            // UI Reset
            loading.classList.remove('hidden');
            error.classList.add('hidden');
            result.classList.add('hidden');

            try {
                const res = await fetch(`/api/market/price?world=${world}&item_id=${itemId}`);
                const data = await res.json();

                if (!res.ok || data.error) {
                    throw new Error(data.error ?? 'データの取得に失敗しました');
                }

                renderData(data);
                loading.classList.add('hidden');
                result.classList.remove('hidden');
            } catch (e) {
                loading.classList.add('hidden');
                showError(e.message);
            }
        });

        function showError(msg) {
            errorMessage.textContent = msg;
            error.classList.remove('hidden');
        }

        function renderData(data) {
            // Raw JSON
            jsonOutput.textContent = JSON.stringify(data, null, 2);

            // Summary
            document.getElementById('itemName').textContent = `アイテム ID: ${data.itemID}`;

            document.getElementById('minPriceNQ').textContent = data.minPriceNQ > 0 ? formatGil(data.minPriceNQ) : '出品なし';
            document.getElementById('minPriceHQ').textContent = data.minPriceHQ > 0 ? formatGil(data.minPriceHQ) : '出品なし';
            document.getElementById('avgPrice').textContent = formatGil(data.averagePrice);

            // Listings Table
            const tbody = document.getElementById('listingsBody');
            tbody.innerHTML = '';

            if (!data.listings || data.listings.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-8 text-center text-slate-500">現在出品されているアイテムはありません</td></tr>';
                return;
            }

            data.listings.slice(0, 10).forEach(listing => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-slate-50 transition';
                tr.innerHTML = `
                    <td class="px-6 py-4 font-medium ${listing.hq ? 'text-orange-600' : 'text-slate-500'}">
                        ${listing.hq ? 'HQ' : 'NQ'}
                    </td>
                    <td class="px-6 py-4">${formatGil(listing.pricePerUnit)}</td>
                    <td class="px-6 py-4">${listing.quantity}</td>
                    <td class="px-6 py-4 font-semibold">${formatGil(listing.total)}</td>
                    <td class="px-6 py-4 text-sm text-slate-500">${listing.retainerName}</td>
                `;
                tbody.appendChild(tr);
            });
        }
    </script>
</body>

</html>