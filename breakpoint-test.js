import http from 'k6/http';
import { sleep } from 'k6';

const tokens = JSON.parse(open('./tokens.json'));

export let options = {
  scenarios: {
    breaking_test: {
      executor: 'ramping-vus',

      stages: [
        { duration: '30s', target: 50 },
        { duration: '30s', target: 100 },
        { duration: '30s', target: 200 },
        { duration: '30s', target: 400 },
        { duration: '30s', target: 600 },
      ],

      gracefulRampDown: '10s',
    },
  },
};

function randomProductId() {
  return Math.floor(Math.random() * 300) + 1;
}

export default function () {
  const token = tokens[__VU % tokens.length];

  const headers = {
    Authorization: `Bearer ${token}`,
    'Content-Type': 'application/json',
  };

  // Browse
  http.get('http://localhost:8080/api/v1/products', { headers });

  // Add to cart
  http.post(
    'http://localhost:8080/api/v1/cart/add',
    JSON.stringify({
      product_id: randomProductId(),
      quantity: 1,
    }),
    { headers }
  );

  // Checkout
  const res = http.post(
    'http://localhost:8080/api/v1/orders/checkout',
    null,
    { headers }
  );

  // Log server errors only
  if (res.status >= 500) {
    console.log(`ERROR ${res.status}: ${res.body}`);
  }

  sleep(1);
}