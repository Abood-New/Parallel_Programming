import http from 'k6/http';

export let options = {
    vus: 1,
    iterations: 1,
};

const BASE_URL = 'http://localhost:8000/api/v1/orders/checkout?mode=unsafe';

const tokens = [
    'QdpWT3AGs3QgPlLa7lzAvcPOLEOUsBnZgraLgjgE55c54789',
    'JeEendAzlSb8UgfofTKMxonN4FRV5fcLWKrhPE7R8ad24cfa',
    'jeafOiqAB10jmYxFpq2cY0EqxqLTaGrLKYiPWo0f2a5cb676',
    '4sSNjix9GplJipx200xKD2Jg9yEmdYBZa5bkQgbke9db4719',
    'aVLxJdWTb5qzHlDNSqA7vlV5WpM2rFghYyxEI0vg6022e2f1'
];

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
