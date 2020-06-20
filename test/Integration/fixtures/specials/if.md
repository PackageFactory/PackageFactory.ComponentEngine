```jsx
<section>
    <header c:if={props.hasHeadline}>
        <h1>{props.headline}</h1>
    </header>

    {props.content}
</section>