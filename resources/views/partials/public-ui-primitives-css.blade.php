        :root {
            --public-card-border: 1px solid var(--line, rgba(255, 255, 255, .14));
            --public-card-bg: rgba(255, 255, 255, .02);
            --public-card-radius: .9rem;
            --public-card-padding: 1rem;
            --public-btn-radius: 999px;
            --public-btn-padding: .62rem 1rem;
            --public-btn-weight: 700;
            --public-input-radius: .55rem;
            --public-input-padding: .68rem .75rem;
        }
        .ui-card {
            border: var(--public-card-border);
            border-radius: var(--public-card-radius);
            background: var(--public-card-bg);
            padding: var(--public-card-padding);
        }
        .ui-input {
            width: 100%;
            border: 1px solid rgba(255, 255, 255, .2);
            border-radius: var(--public-input-radius);
            padding: var(--public-input-padding);
            background: rgba(255, 255, 255, .04);
            color: #fff;
        }
        .ui-btn {
            border: 0;
            border-radius: var(--public-btn-radius);
            padding: var(--public-btn-padding);
            font-weight: var(--public-btn-weight);
            cursor: pointer;
        }
        .ui-btn-accent {
            background: linear-gradient(135deg, #f3c469, #d89a2b);
            color: #1d1204;
        }
        .ui-chip {
            border: 1px solid var(--line, rgba(255, 255, 255, .14));
            border-radius: 999px;
            padding: .35rem .7rem;
            color: var(--muted, rgba(255, 255, 255, .72));
            font-size: .84rem;
            display: inline-flex;
            align-items: center;
            line-height: 1;
        }
