#!/bin/sh
set -eu
for source in 'https://whatthefrog.pl' 'http://whatthefrog.pl' 'https://www.whatthefrog.pl' 'http://www.whatthefrog.pl'; do
    echo "Replacing $source"
    if wp search-replace "$source" 'http://localhost:8082' --all-tables --skip-tables='*actionscheduler*,*woocommerce_sessions,*wf*,*aioseo_redirects*logs' --skip-columns=guid --report-changed-only --allow-root --skip-themes > /backup/url-replacement.log 2>&1; then
        tail -n 12 /backup/url-replacement.log
    else
        tail -n 30 /backup/url-replacement.log
        exit 1
    fi
done
