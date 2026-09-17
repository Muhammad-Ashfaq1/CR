{{--
    Shared AJAX table shell.

    Wrap a server-paginated table in this and its pagination stops being page
    navigation: awt-table.js fetches the next page, swaps the markup inside the
    viewport, and leaves the browser URL — and the scroll position — exactly
    where they were.

        <x-awt-table id="usage-ai-events" :state="['page' => $rows->currentPage(), 'per_page' => $perPage]">
            ...table, footer, pagination links...
        </x-awt-table>

    Contract for what goes inside:
      • pagination links render as usual ({{ $rows->links() }}) — anything inside
        a .pagination is intercepted;
      • a rows-per-page / filter form carries data-awt-table-form;
      • a filter link carries data-awt-table-link.
    Everything else (row actions, View buttons, mailto:) is left alone.

    `state` is the table's CURRENT state, and it is also the whitelist: only
    these keys are persisted to sessionStorage and only these are read back out
    of a URL. Pass page/per_page and any filter that is safe to remember; never
    pass a record id, a token or a cursor.

    Without JavaScript the links inside are still ordinary GET links, so the
    table keeps working (that path does show ?page= in the URL, as it did
    before).
--}}
@props(['id', 'state' => []])

<div class="awt-table"
     data-awt-table
     data-awt-table-id="{{ $id }}"
     data-awt-table-scope="{{ \App\Support\TableFragment::scopeToken() }}"
     data-awt-table-route="{{ optional(request()->route())->getName() ?: request()->path() }}"
     data-awt-table-endpoint="{{ url()->current() }}"
     data-awt-table-state="{{ json_encode((object) $state) }}"
     {{ $attributes }}>
    <div class="awt-table-viewport" data-awt-table-viewport>
        {{ $slot }}
    </div>
</div>
