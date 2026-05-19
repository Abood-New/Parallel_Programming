import http from 'k6/http';
import { sleep, check } from 'k6';

// 🔥 Load tokens (one per user)
const tokens = JSON.parse(open('./tokens.json'));

// 🔥 Config: realistic load pattern
export let options = {
    scenarios: {
        user_journey: {
            executor: 'constant-vus',
            vus: 200,
            duration: '60s',
        },
    },
};

// 🔥 Helper: pick random product
function randomProductId() {
    return Math.floor(Math.random() * 200) + 1; // adjust to your dataset
}

export default function () {
    const token = tokens[__VU % tokens.length];

    const headers = {
        Authorization: `Bearer ${token}`,
        'Content-Type': 'application/json',
    };

    // 1️⃣ View products (simulate browsing)
    let res = http.get('http://localhost:8000/api/v1/products', { headers });

    check(res, {
        'products loaded': (r) => r.status === 200,
    });

    sleep(Math.random() * 2); // thinking time (0–2s)

    // 2️⃣ Add 2–3 items to cart
    const itemsToAdd = Math.floor(Math.random() * 2) + 2;

    for (let i = 0; i < itemsToAdd; i++) {
        const productId = randomProductId();

        let addRes = http.post(
            'http://localhost:8000/api/v1/cart/add',
            JSON.stringify({
                product_id: productId,
                quantity: 1,
            }),
            { headers }
        );

        check(addRes, {
            'added to cart': (r) => r.status === 200,
        });

        sleep(Math.random()); // small delay
    }

    sleep(Math.random() * 2); // thinking time

    // 3️⃣ Checkout (ONLY ONCE per iteration)
    let checkoutRes = http.post(
        'http://localhost:8000/api/v1/orders/checkout',
        null,
        { headers }
    );

    check(checkoutRes, {
        'checkout success or valid failure': (r) =>
            r.status === 200 || r.status === 400,
    });

    // Optional: log unexpected errors
    if (checkoutRes.status >= 500) {
        console.log(`SERVER ERROR: ${checkoutRes.status}`);
    }

    sleep(1); // user cooldown
}