import http from 'k6/http';

export let options = {
    vus: 1,
    iterations: 1,
};

const BASE_URL = 'http://localhost:8000/api/v1/orders/checkout?mode=unsafe';

const tokens = JSON.parse(open('./tokens.json'));

export default function() {
    const requests = tokens.map(token => ({
        method: 'POST',
        url: BASE_URL,
        body: JSON.stringify({}),
        params: {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
            },
        },
    }));

    let responses = http.batch(requests);

    responses.forEach((res, i) => {
        console.log(`User ${i + 1} → ${res.status}`);
    });
}
